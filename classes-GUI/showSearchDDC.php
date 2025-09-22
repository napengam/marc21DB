<?php


require '../include/core.inc.php';

class showDDC {

    use httpRequest;

    function __construct() {
       
        $this->readRequest();

        $talk = new websocketPhp(GetAllConfig::load()['websocketserver']['adress'] . '/php');
        $talk->uuid = $this->param->uuid; // client uuid to talk back

        $qs = "select titleid as id from search where colname=? and match(what) against(? in boolean mode) ";

        $q = "select d.descript, t.ddc , count(t.ddc) as num from titles as t ,ddc as d  
            where  d.ddc=t.ddc and d.isolang='de' and t.id in ($qs)  group by ddc";

        $rows = PDODB::getInstance('marc21')->query($q,[$this->param->colname, $this->param->search]);
       
        $out = [];

        $talk->feedback("Erstelle Facette DDC");
        $out[] = "<div id='ddcover' class='scroller' >";
        $out[] = "<table id='ddctable' class='table is-size-7'>"
                . "<thead style='background-color:white'>"
                . "<tr><th>DDC</th><th>Anzahl</th><th>Beschreibung</th></tr>"
                . "</thead>";

        foreach ($rows as $row) {
            $out[] = "<tr data-ddc='$row->ddc' >"
                    . "<td>$row->ddc</td><td>$row->num</td><td>$row->descript</td>"
                    . "</tr>";
        }
        $out[] = "</table>";
        $out[] = "</div>";

        $result[0] = implode('', $out);

        $this->param->result = $result;
        echo $this->closeRequest($this->param);
    }
}

$xx = new showDDC();
