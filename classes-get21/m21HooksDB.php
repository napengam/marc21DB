<?php

class m21HooksDB {

    private $db, $updateddc, $search;

    function __construct() {
        $this->db = PDODB::getInstance('marc21');
        $q = "update titles set ddc=? where id=?";
        $this->updateddc = $this->db->prepare($q);
        // fill full text index
        $this->search = new insertSearch($this->db);
    }

    public function hookAfterTitleInsert($id, $tags) {

        /*
         * ***********************************************
         * add ddc to title
         * **********************************************
         */
        $ddc = $tags->ddc();
        echo "$id / $ddc \r\n";
        $this->db->query($this->updateddc, [$ddc, $id]);
        $this->search->insert($id);
    }
}
