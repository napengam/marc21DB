<?php

class Tags2MemNew {

    private  $db;
    private array $tags = [];
    private array $tagIndex = [];
    private array $consumed = [];

    public function __construct() {
        $this->db = PDODB::getInstance('marc21');
    }

    /**
     * Internal setup: load tags, add synthetic tag, build index, init consumed map
     */
    function setTags(string $titleId, string $filter = ''): void {
        
        $this->tags = [];
        $this->tagIndex = [];
        $this->consumed = [];

        $sqlFilter = $filter !== '' ? " AND tag IN ($filter) " : '';

        $q = "
            SELECT tag, seq, indicator, subfieldcode, subfielddata
            FROM tags
            WHERE titleid = '$titleId'
            $sqlFilter
            ORDER BY tag, seq, subfieldcode ASC
        ";

        $this->tags = $this->db->query($q);

        // Add synthetic A00
        $this->addSyntheticA00($titleId);

        // Build tag index
        $this->buildTagIndex();

       
    }

    private function addSyntheticA00(string $titleId): void {
        $q = "
            SELECT SUBSTRING(file,1,5) AS syw
            FROM sources
            WHERE id = (SELECT sourceid FROM titles WHERE id='$titleId')
        ";

        $syw = $this->db->query($q);
        $val = $syw[0]->syw ?? '';

        $this->tags[] = (object) [
                    'tag' => 'A00',
                    'seq' => 1,
                    'indicator' => '',
                    'subfieldcode' => 'a',
                    'subfielddata' => $val
        ];
    }

    private function buildTagIndex(): void {
        $this->tagIndex = [];
        $last = null;

        foreach ($this->tags as $i => $t) {
            if ($t->tag !== $last) {
                $this->tagIndex[$t->tag] = $i;
                $last = $t->tag;
            }
        }
    }

    /**
     * Retrieve a subfield by tag, seq, and code.
     * Automatically marks it as consumed.
     */
    public function getData(string $tag, int $seq, string $code, bool $consume = true): ?string {
        if (!isset($this->tagIndex[$tag])) {
            return null;
        }

        $start = $this->tagIndex[$tag];
        $n = count($this->tags);

        for ($i = $start; $i < $n; $i++) {
            $t = $this->tags[$i];

            if ($t->tag !== $tag) {
                break;
            }

            $matchCode = ($t->subfieldcode === $code || $code === '');
            $isConsumed = $this->consumed[$i] ?? false;

            if ($t->seq === $seq && $matchCode && !$isConsumed) {
                if ($consume) {
                    $this->consumed[$i] = true;
                }
                return $t->subfielddata;
            }
        }

        return null;
    }

    /**
     * Reset consumed map internally
     */
    public function reset(): void {
        $this->consumed = [];
    }

    /**
     * Return all tags with "!" markers applied for consumed subfields
     */
    public function getBangMarkedTags(): array {
        $out = [];

        foreach ($this->tags as $i => $t) {
            $copy = clone $t;
            if (!empty($this->consumed[$i])) {
                $copy->subfieldcode = '!' . $copy->subfieldcode;
            }
            $out[] = $copy;
        }

        return $out;
    }
}
