<?php

class m21HooksDB {

    private $updateddc;

    function __construct() {
        global $connect_pdo;
        $q = "update titles set ddc=? where id=?";
        $this->updateddc = $connect_pdo->prepare($q);
    }

    public function hookAfterTitleInsert($id, $tags) {

        /*
         * ***********************************************
         * add ddc to title
         * **********************************************
         */
        $ddc = substr($tags->ddc082(), 0, 3);
        echo "$id / $ddc \r\n";
        $this->updateddc->execute([$ddc, $id]);
    }
}
