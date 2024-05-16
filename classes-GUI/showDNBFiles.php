<?php

require '../include/core.inc.php';

class showDNBFiles {

    use httpRequest;

    function __construct() {

        $this->readRequest();

        $rows = filesFromDB::show();
        $out = [];
        $out[] = "<select id='selector' onchange='marc21DB.showTitles(this)'>";
        $out[] = "<option selected disabled hidden> -- Wähle-- </option>";
        $selected = 'selected';
        foreach ($rows as $row) {
            $out[] = "<option $selected data-id='$row->id' data-name='$row->file / $row->nrecs'>$row->yy-$row->ww $row->se $row->nrecs </option>";
            $selected = '';
        }
        $out[] = "</select>";

        $this->param->newest = $rows[0]->id;
        $this->param->result = implode('<br>', $out);
        echo $this->closeRequest($this->param);
    }

    //put your code here
}

$x = new showDNBFiles();
