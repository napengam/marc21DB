<?php

class filesFromDB {

    private static $db;

    public static function show() {

        require '../include/connect.inc.php';

        $q = "SELECT s.id,file, "
                . "concat('20',substring(file,2,2)) as yy ,"
                . "substring(file,4,2) as ww, "
                . "substring(file,1,1) as se, "
                . "count(file) as nrecs FROM `sources` as s ,titles as t "
                . "WHERE s.id=t.sourceid group by yy ,ww ,se order by yy, ww desc";
        
        $res = $connect_pdo->query($q);
        $rows = $res->fetchAll();
        return $rows;
    }
}
