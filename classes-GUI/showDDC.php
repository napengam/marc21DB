<?php

require_once '../include/core.inc.php';

class showDDC {

    use httpRequest;

    function __construct() {
        $this->readRequest();
        $Adress = GetAllConfig::load()['websocketserver']['adress'];
        $talk = new websocketPhp($Address . '/php');
        $talk->uuid = $this->param->uuid; // client uuid to talk back

        $q = "select d.descript, t.ddc , count(t.ddc) as num from titles as t ,ddc as d  
            where sourceid=? and d.ddc=t.ddc and d.isolang='de' group by ddc order by ddc";
        $db = PDODB::getInstance('marc21');
        $ttt = $db->prepare($q);
        $rows = $db->query($ttt, [$this->param->id]);
        $out = [];

        $talk->feedback("Erstelle Facette DDC");
        $out[] = "<div id='ddcover' class='scroller' >";
        $out[] = "<table id='ddctable' class='table is-size-7 is-hoverable'>"
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
