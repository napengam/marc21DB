<?php

class marc21toDB extends m21File {

    private $insertTags = "insert into tags (titleid,tag,seq,indicator,subfieldcode,subfielddata) values ",
            $insertTitles = "insert into titles (sourceid,offset) values (?,?)",
            $insertSources = "insert into sources (path,file) values (?,?) ON DUPLICATE KEY UPDATE file=file",
            $block = true, $db, $insT, $insS, $sourceid;
    public $hook;

    function __construct($db) {
        $this->db = $db;
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
        $ex = $this->db->prepare($q);
        $ex->execute([$pi->dirname, $pi->basename]);
        return $ex->rowCount();
    }

    public function fileDelete($m21File) {
        $pi = (object) pathinfo($m21File);
        $q = "delete from sources where path=? and file=?";
        $ex = $this->db->prepare($q);
        $ex->execute([$pi->dirname, $pi->basename]);
        return $ex->rowCount();
    }

    private function insertSource($m21File) {
        $pi = (object) pathinfo($m21File);
        $this->insS->execute([$pi->dirname, $pi->basename]);
        if ($this->insS->rowCount() !== 1) {
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
            $allTags = new getTagData($this->db);
        }

        $placeh = "(?,?,?,?,?,?)";
        while (($tags = $this->decodeRecord()) !== NULL) {
            $this->insT->execute([$this->sourceid, $this->recordOffset]);
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
            $ins = $this->db->prepare($query);
            $ins->execute($values);
            if ($go) {
                $allTags->getAllTags($titleid);
                $this->hook->hookAfterTitleInsert($titleid, $allTags);
            }
        }
    }

    public function setHooks($hook) {
        $this->hook = $hook;
    }
}
