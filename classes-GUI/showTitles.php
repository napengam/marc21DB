<?php

require '../include/connect.inc.php';
require '../include/core.inc.php';
include '../include/adressPort.inc.php';

class showTitles {

    use httpRequest;

    function __construct() {
        global $connect_pdo, $Address;
        $this->readRequest();
        /*
         * ***********************************************
         * register with websocket for feedback
         * **********************************************
         */
        $talk = new websocketPhp($Address . '/php');
        $talk->uuid = $this->param->uuid; // client uuid to talk back


        $xx = new titleData($connect_pdo, $this->param);
        if ($this->param->search !== '') {
            $q = "select titleid as id from search where colname=? and match(what) against(? in boolean mode) ";
            $ttt = $connect_pdo->prepare($q);
            $ttt->execute([$this->param->colname, $this->against($this->param->search)]);
        } else if ($this->param->ddc !== '') {
            $q = "select id from titles where sourceid=? and ddc=?";
            $ttt = $connect_pdo->prepare($q);
            $ttt->execute([$this->param->id, $this->param->ddc]);
        } else {
            $q = "select id from titles where sourceid=?";
            $ttt = $connect_pdo->prepare($q);
            $ttt->execute([$this->param->id]);
        }
        $rows = $ttt->fetchAll();
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

    function against($words) {
        $words = preg_replace('/\s+/', ' +', " " . trim($words));
        return $words;
        
    }
}

$xx = new showTitles();
