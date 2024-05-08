<?php

/*
 * ***********************************************
 * read tags for one title into memory and
 * access them.
 * **********************************************
 */

require_once '../include/connect.inc.php';

class tags2mem {

    private $db, $tags, $filter = '';

    function __construct($db) {
        $this->db = $db;
    }

    function setFilter($filter) {
        $this->filter = $filter;
    }

    function setTags($titleid) {
        $this->tags = [];
        $tagFilter = '';
        if ($this->filter) {
            $tagFilter = " and tags in ($this->filter) ";
        }
        $q = "select tag,seq,indicator,subfieldcode,subfielddata, 0 as consumed from tags where titleid='$titleid' $tagFilter ";
        $ta = $this->db->query($q);
        $this->tags = $ta->fetchAll();

        /*
         * ***********************************************
         * fake tag A00 to hold Series and year and week
         * **********************************************
         */

        $q = "select substring(file,1,5) as syw from sources where id=(select sourceid from titles where id='$titleid')";
        $ss = $this->db->query($q);
        $syw = $ss->fetch();

        $this->tags[] = (object) ['tag' => 'A00', 'seq' => 1, 'indicator' => '',
                    'subfieldcode' => 'a', 'subfielddata' => $syw->syw, 'consumed' => 0];
        /*
         * ***********************************************
         * sort for binary search to work
         * **********************************************
         */
        usort($this->tags, [$this, "compare"]);
        return $this->tags;
    }

    function getData($tag, $seq, $code, $consumed = true) {

        $p = $this->fast_in_array($this->tags, $tag);
        if ($p === -1) {
            return null; // no such tag
        }

        for (; $p < count($this->tags); $p++) {
            $aTag = $this->tags[$p];
            if ($aTag->tag !== $tag) {
                break; // not found 
            }
            if ($aTag->seq === $seq && ($aTag->subfieldcode === $code || $code == '') && !$aTag->consumed) {
                $aTag->consumed = $consumed;
                return $aTag->subfielddata;
            }
        }
        return null;
    }

    function reset() {
        foreach ($this->tags as &$aTag) {
            $aTag->consumed = false;
        }
    }

    private function fast_in_array($tags, $tag) {

        if (strlen(trim($tag)) == 0) {
            return -1;
        }
        $top = sizeof($tags) - 1;
        $bot = 0;

        while ($top >= $bot) {
            $p = floor(($top + $bot) / 2);
            if ($tags[$p]->tag < $tag) {
                $bot = $p + 1;
            } else if ($tags[$p]->tag > $tag) {
                $top = $p - 1;
            } else {
                for ($p - 1; $p >= 0; $p--) {
                    if ($tags[$p]->tag !== $tag) {
                        $p++;
                        break;
                    }
                }
                return $p;
            }
        }
        return -1;
    }

    private function compare($a, $b) {
        if ($a->tag < $b->tag) {
            return -1;
        }
        if ($a->tag > $b->tag) {
            return 1;
        }
        if ($a->tag == $b->tag) {
            if ($a->seq < $b->seq) {
                return -1;
            }
            if ($a->seq > $b->seq) {
                return 1;
            }
        }
        return 0;
    }
}
