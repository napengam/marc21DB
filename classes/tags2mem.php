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
        $q = "select tag,seq,indicator,subfieldcode,subfielddata, 0 as consumed from tags where titleid='$titleid' $tagFilter";
        $ta = $this->db->query($q);
        $this->tags = $ta->fetchAll();
        return $this->tags;
    }

    function getData($tag, $seq, $code, $consumed = true) {

        foreach ($this->tags as $aTag) {
            if ($aTag->tag === $tag && $aTag->seq === $seq && ($aTag->subfieldcode === $code || $code == '') && !$aTag->consumed) {
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
}