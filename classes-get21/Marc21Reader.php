<?php

/**
 * Description of marc21
 * @author Heinz
 *
 * Requirements: ext-intl, ext-mbstring
 */
class Marc21Reader {

    private $fh;
    private $filter = '';
    private $leader = '';
    private $dict = '';
    private $data = ''; // Changed from array to string for performance
    private $nRecords = 0;
    // Public properties kept as requested
    public $recordOffset = 0;
    public $pos67 = '';
    public $error = '';
    public $nonSortShow = false;

    // Constants for MARC delimiters
    const SUB_SEP = "\x1F";
    const END_FLD = "\x1E";
    const END_REC = "\x1D";

    function openM21($m21File) {
        if (file_exists($m21File)) {
            $this->fh = fopen($m21File, 'rb');
            $this->filter = '';
            $this->nRecords = 0;
            $this->data = '';
        } else {
            $this->error = "File " . basename($m21File) . " does not exist";
        }
    }

    function decodeRecord() {
        if (!$this->readM21Record()) {
            return NULL;
        }

        $tagInd = [];

        // ------------------------------------
        // 1. Construct Leader (Tag '000')
        // ------------------------------------
        // Check filter: if filter is set and '000' is NOT in it, skip.
        // Original logic: strpos($this->filter, '000') === false -> skip
        if (!$this->filter || strpos($this->filter, '000') !== false) {
            $leaderObj = (object) [
                        'tag' => '000',
                        'ind' => '  ',
                        'seq' => '1',
                        'subs' => [
                            (object) ['code' => 'a', 'data' => $this->leader]
                        ]
            ];
            $tagInd[] = $leaderObj;
        }

        // ------------------------------------
        // 2. Iterate Directory
        // ------------------------------------
        // Directory entries are exactly 12 bytes long.
        $dirLen = strlen($this->dict);
        $refTag = '';
        $seq = 0;

        for ($i = 0; $i < $dirLen; $i += 12) {
            // Safety check for end of directory
            if ($this->dict[$i] === self::END_FLD) {
                break;
            }
            $entry = substr($this->dict, $i, 12);
            if (strlen($entry) < 12) {
                break;
            }

            $tag = substr($entry, 0, 3);

            // Filter Logic (Preserving original logic: '001' is special unless specifically filtered out?)
            // Original code says: if ($this->filter && $tag !== '001') check strpos.
            if ($this->filter && $tag !== '001') {
                if (strpos($this->filter, $tag) === false) {
                    continue;
                }
            }

            $len = (int) substr($entry, 3, 4);
            $offset = (int) substr($entry, 7, 5);

            // Sequence handling
            if ($tag != $refTag) {
                $seq = 1;
                $refTag = $tag;
            } else {
                $seq++;
            }

            $oneTag = (object) ['tag' => $tag, 'ind' => '  ', 'seq' => $seq, 'subs' => []];

            // ------------------------------------
            // 3. Extract Raw Data (Byte Safe)
            // ------------------------------------
            // -1 removes the Field Terminator from the end of the length
            $rawField = substr($this->data, $offset, $len - 1);

            // ------------------------------------
            // 4. Parse Indicators
            // ------------------------------------
            if ($tag >= '010') {
                $oneTag->ind = substr($rawField, 0, 2);
                $subData = substr($rawField, 2);
            } else {
                // Control fields (00X) have no indicators and no subfield separators
                $subData = $rawField;
            }

            // ------------------------------------
            // 5. Parse Subfields
            // ------------------------------------
            if ($tag >= '010') {
                // Split by Unit Separator (1F)
                // This replaces the complex while loop
                $parts = explode(self::SUB_SEP, $subData);

                // If string starts with 1F, first part is empty. 
                // If it doesn't (rare error), first part is data before first subfield.
                // Standard MARC21 always starts data fields with 1F after indicators, 
                // but we filter empty just in case.

                $s = 0;
                foreach ($parts as $k => $part) {
                    if ($k === 0 && $part === '') {
                        continue; // skip empty start
                    }
                    if ($part === '') {
                        continue;
                    }

                    $code = $part[0]; // First char is code
                    $value = substr($part, 1); // Rest is data

                    $oneTag->subs[$s] = (object) [
                                'code' => $code,
                                'data' => $this->finalizeData($value)
                    ];
                    $s++;
                }
            } else {
                // Control Field (001, 005, etc) - simple data, no subfields
                // Original code structure mapped this to subfield '' ? 
                // Actually, original code loop logic would assign it to data if no 1F found.
                // We usually map control fields to subfield 'a' or empty code.
                // Based on original loop: if no 1F found, it adds one sub with empty code.
                $oneTag->subs[0] = (object) [
                            'code' => '',
                            'data' => $this->finalizeData($subData)
                ];
            }

            $tagInd[] = $oneTag;
        }

        return $tagInd;
    }

    /**
     * Helper to handle Non-Sort chars and UTF-8 Normalization
     */
    private function finalizeData($input) {
        // Handle Non-Sort { 0xC2 0x98 } and { 0xC2 0x9C }
        if ($this->nonSortShow) {
            // This is the string replacement version of the hex check
            // \xC2\x98 = 194 152
            // \xC2\x9C = 194 156
            $input = str_replace(
                    ["\xC2\x98", "\xC2\x9C"],
                    ['{{{', '}}}'],
                    $input
            );
        }

        // Normalize NFD to NFC
        return Normalizer::normalize($input, Normalizer::FORM_C);
    }

    function setTagFilter($filter) {
        if ($filter !== '') {
            $this->filter = ' ' . $filter . '|';
        }
    }

    function setPosition($offset) {
        if ($this->fh) {
            fseek($this->fh, $offset);
            $this->recordOffset = $offset;
        }
    }

    function skipRecord() {
        $this->recordOffset = ftell($this->fh);
        $this->leader = fread($this->fh, 24);
        if (feof($this->fh) || strlen($this->leader) < 24) {
            return false;
        }

        $reclen = (int) substr($this->leader, 0, 5);
        $dataoffset = (int) substr($this->leader, 12, 5);

        // Just seek forward
        $remaining = $reclen - 24;
        if ($remaining > 0) {
            fseek($this->fh, $remaining, SEEK_CUR);
            $this->nRecords++;
            return true;
        }

        $this->nRecords++;
        $this->error .= "\r\nAb Record $this->nRecords, bei Offset $this->recordOffset, kann nicht weiter gelesen werden";
        return false;
    }

    private function readM21Record() {
        $this->recordOffset = ftell($this->fh);
        $this->leader = fread($this->fh, 24);

        if (feof($this->fh) || strlen($this->leader) < 24) {
            return false;
        }

        $this->pos67 = mb_substr($this->leader, 6, 2); // kept mb_substr for pos67 logic if needed
        $reclen = (int) substr($this->leader, 0, 5);
        $dataoffset = (int) substr($this->leader, 12, 5);

        if ($reclen - $dataoffset > 0) {
            // Read Directory
            $this->dict = fread($this->fh, $dataoffset - 24);
            // Read Data (Now stored as string, not array)
            $this->data = fread($this->fh, $reclen - $dataoffset);

            $this->nRecords++;
            return true;
        } else {
            $this->error .= "\r\nAb Record $this->nRecords, bei Offset $this->recordOffset, kann nicht weiter gelesen werden";
            return false;
        }
    }
}

?>