<?php

class filesFromDB {

    public static function show() {

        $q = "SELECT s.id,file, "
                . "concat('20',substring(file,2,2)) as yy ,"
                . "substring(file,4,2) as ww, "
                . "substring(file,1,1) as se, "
                . "count(file) as nrecs FROM `sources` as s ,titles as t "
                . "WHERE s.id=t.sourceid group by yy ,ww ,se order by yy, ww desc";

        $rows = PDODB::getInstance('marc21')->query($q);

        return $rows;
    }
}
