<?php

require '../include/core.inc.php';

class showTitles {

    use httpRequest;

    function __construct() {

        $this->readRequest();
        /*
         * ***********************************************
         * register with websocket for feedback
         * **********************************************
         */
        $talk = new websocketPhp(GetAllConfig::load()['websocketserver']['adress'] . '/php');
        $talk->uuid = $this->param->uuid; // client uuid to talk back

        $db = PDODB::getInstance('marc21');

        $xx = new titleData($this->param);
        if ($this->param->search !== '') {
            $q = "select titleid as id from search where colname=? and match(what) against(? in boolean mode) ";        
            $rows = $db->query($q, [$this->param->colname, $this->against($this->param->search)]);
        } else if ($this->param->ddc !== '') {
            $q = "select id from titles where sourceid=? and ddc=?";          
            $rows = $db->query($q, [$this->param->id, $this->param->ddc]);
        } else {
            $q = "select id from titles where sourceid=?";          
            $rows = $db->query($q, [$this->param->id]);
        }

        $res = $ids = [];

        $j = 0;
        $m = $this->param->cursor['max'];
        foreach ($rows as $i => $row) {
            $ids[] = $row->id;
            if ($m > 0 && $i >= $m) {
                continue;
            }
            $j++;
            $out = $xx->makeISBD($row->id);
            if ($this->param->search !== '') {
                //   $out = $this->yellow($this->param->search, $out);
            }
            $res[] = "<div  class = 'box'  >"
                    . "<div class='content is-family-sans-serif'>$out<br></div></div>";
            if ($i % 200 == 0) {
                $talk->feedback("Lese $i von" . count($rows) . ' Titel');
            }
        }
        $this->param->ntitles = $j;
        $this->param->ids = $ids;
        $this->param->result = implode('', $res);
        echo $this->closeRequest($this->param);
    }

    function against(string $words): string {
        $words = trim($words);

        // Unicode-safe word extraction
        preg_match_all('/[\p{L}\p{N}_\*]+/u', $words, $matches);
        $arr = $matches[0];

        $cleaned = array_map(function ($w) {
            // Remove leading stars, allow trailing
            $w = preg_replace('/^\*+/', '', $w);
            // Collapse multiple trailing stars into one
            $w = preg_replace('/\*+$/', '*', $w);
            return $w;
        }, $arr);

        // Build boolean query
        if (count($cleaned) > 1) {
            return '+' . implode(' +', $cleaned);
        }

        return $cleaned[0] ?? '';
    }
}

$xx = new showTitles();
