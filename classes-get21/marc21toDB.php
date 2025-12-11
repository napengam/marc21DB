<?php

class marc21toDB extends Marc21Reader {

    private $insertTags = "insert into tags (titleid,tag,seq,indicator,subfieldcode,subfielddata) values ",
            $insertTitles = "insert into titles (sourceid,offset) values (?,?)",
            $insertSources = "insert into sources (path,file) values (?,?) ON DUPLICATE KEY UPDATE file=file",
            $block = true, $db, $insT, $insS, $sourceid,
            $allTags = null;
    public $hook;

    function __construct() {
        $this->db = PDODB::getInstance('marc21');
    }

    function readFile($m21File) {
        $this->openM21($m21File);
        if ($this->error) {
            return;
        }
        $this->block = false;
        $this->insT = $this->db->prepare($this->insertTitles);
        $this->insS = $this->db->prepare($this->insertSources);
        return $this->insertSource($m21File);
    }

    public function fileExists($m21File) {
        $pi = (object) pathinfo($m21File);
        $q = "select id from sources where path=? and file=?";
        $this->db->query($q, [$pi->dirname, $pi->basename]);
        return $this->db->rowCount();
    }

    public function fileDelete($m21File) {
        $pi = (object) pathinfo($m21File);
        $q = "delete from sources where path=? and file=?";
        $this->db->query($q, [$pi->dirname, $pi->basename]);
        return $this->db->rowCount();
    }

    private function insertSource($m21File) {
        $pi = (object) pathinfo($m21File);
        $this->db->query($this->insS, [$pi->dirname, $pi->basename]);
        if ($this->db->rowCount() !== 1) {
            $this->block = true;
            return false;
        }
        $this->sourceid = $this->db->lastInsertId();
        return true;
    }

    function insertTitles() {
        if ($this->block) {
            return false;
        }
        $go = false;
        if (is_callable([$this->hook, 'hookAfterTitleInsert'])) {
            $go = true;
            $this->allTags = $this->allTags ?? $this->allTags = new isbdElements($this->db);
        }

        $placeh = "(?,?,?,?,?,?)";
        while (($tags = $this->decodeRecord()) !== NULL) {
            $this->db->query($this->insT, [$this->sourceid, $this->recordOffset]);
            $titleid = $this->db->lastInsertId();
            $tn = 0;
            $values = [];
            foreach ($tags as $tag) {
                foreach ($tag->subs as $subs) {
                    $values[] = $titleid;
                    $values[] = $tag->tag;
                    $values[] = $tag->seq;
                    $values[] = $tag->ind;
                    $values[] = $subs->code;
                    $values[] = $subs->data;
                    $tn++;
                }
            }
            $query = $this->insertTags . implode(',', array_fill(0, $tn, $placeh));
            $this->db->query($query, $values);

            if ($go) {
                $this->allTags->getAllTags($titleid);
                $this->hook->hookAfterTitleInsert($titleid, $this->allTags);
            }
        }
    }

    public function setHooks($hook) {
        $this->hook = $hook;
    }
}
