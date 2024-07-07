<?php

/*
 * ***********************************************
 * read tags for one title into memory and
 * access them.
 * **********************************************
 */

require_once '../include/connect.inc.php';

class tags2mem {

    private $db, $tags, $tagIndex, $filter = '';

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
            $tagFilter = " and tag in ($this->filter) ";
        }
        $q = "select tag,seq,indicator,subfieldcode,subfielddata, 0 as consumed 
                from tags where titleid='$titleid' $tagFilter order by tag,seq,subfieldcode asc";
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

        $this->tagIndex();
        return $this->tags;
    }

    function getData($tag, $seq, $code, $consumed = true) {

        if (!isset($this->tagIndex[$tag])) {
            return null;
        }
        $p = $this->tagIndex[$tag];

        for (; $p < count($this->tags); $p++) {
            $aTag = $this->tags[$p];
            if ($aTag->tag !== $tag) {
                break; // not found, out of tag block
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

    function tagIndex() {
        $this->tagIndex = [];
        $n = count($this->tags);
        $refTag = '';
        for ($i = 0; $i < $n; $i++) {
            $aTag = $this->tags[$i]->tag;
            if ($aTag !== $refTag) {
                $this->tagIndex[$aTag] = $i;
                $refTag = $aTag;
            }
        }
    }
}
