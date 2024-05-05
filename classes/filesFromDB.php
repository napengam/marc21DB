<?php

class filesFromDB {

    private static $db;

    public static function show() {

        require '../include/connect.inc.php';

        $q = "SELECT s.id,s.file as file, count(t.sourceid) as nrecs FROM `sources` as s , titles as t "
                . "where s.id=t.sourceid group by file order by SUBSTRING(file,2) desc";

        $q = "SELECT s.id,file, concat('20',substring(file,2,2)) as yy ,substring(file,4,2) as ww, substring(file,1,1) as se,count(file) as nrecs FROM `sources` as s ,titles as t where s.id=t.sourceid group by yy desc,ww desc,se";
        $res = $connect_pdo->query($q);
        $rows = $res->fetchAll();
        return $rows;
    }
}
