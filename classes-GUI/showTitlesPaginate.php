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


        $xx = new titleData($connect_pdo);

        if (count($this->param->cursor['ids']) == 0) {
            echo $this->closeRequest($this->param);
            exit;
        }
        $res = [];
        $n = count($this->param->cursor['ids']);
        foreach ($this->param->cursor['ids'] as $i => $id) {
            $out = $xx->makeISBD($id);
            if ($this->param->search !== '') {
                $out = $this->yellow($this->param->search, $out);
            }
            $res[] = "<div  class = 'box'><div class='content is-family-sans-serif'>$out<br></div></div>";
            if ($i % 200 == 0) {
                $talk->feedback("Lese $i von" . $n . ' Titel');
            }
        }


        $this->param->result = implode('', $res);
        echo $this->closeRequest($this->param);
    }

    function yellow($words, $line) {

        $arr = explode('*', $words);
        $words = implode('', $arr);
        $arr = explode(' ', $words);

        $p = [];
        foreach ($arr as $word) {
            $p[] = "$word";
        }

        $p = '/\b' . implode('|\b', $p) . "/i";
        $s = "<span style='background-color:yellow'>" . '$0' . "</span>";

        return preg_replace($p, $s, $line);
    }
}

$xx = new showTitles();
