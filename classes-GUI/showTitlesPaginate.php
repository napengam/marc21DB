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
        $Adress=GetAllConfig::load()['websocketserver']['adress'];
        $talk = new websocketPhp($Adress . '/php');
        $talk->uuid = $this->param->uuid; // client uuid to talk back


        $xx = new titleData($this->param);

        if (count($this->param->cursor['ids']) == 0) {
            echo $this->closeRequest($this->param);
            exit;
        }
        $res = [];
        $n = count($this->param->cursor['ids']);
        foreach ($this->param->cursor['ids'] as $i => $id) {
            $out = $xx->makeISBD($id);            
            $res[] = "<div  class = 'box'><div class='content is-family-sans-serif'>$out<br></div></div>";
            if ($i % 200 == 0) {
                $talk->feedback("Lese $i von" . $n . ' Titel');
            }
        }
        $this->param->result = implode('', $res);
        echo $this->closeRequest($this->param);
    }   
}

$xx = new showTitles();
