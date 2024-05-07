<?php

class m21HooksDB {

    private $updateddc, $search;

    function __construct() {
        global $connect_pdo;
        $q = "update titles set ddc=? where id=?";
        $this->updateddc = $connect_pdo->prepare($q);
        // fill full text index
        $this->search = new insertSearch($connect_pdo);
    }

    public function hookAfterTitleInsert($id, $tags) {

        /*
         * ***********************************************
         * add ddc to title
         * **********************************************
         */
        $ddc = $tags->ddc();
        echo "$id / $ddc \r\n";
        $this->updateddc->execute([$ddc, $id]);
        $this->search->insert($id);
    }
}
