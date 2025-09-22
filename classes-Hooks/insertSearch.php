<?php

/*
 * ***********************************************
 * fill tabel searches with certain column values
 * to allow fulltext serach on these values
 * **********************************************
 */

class insertSearch {

    private $db, $isbd, $insert;

    function __construct($db) {
        $this->db = PDODB::getInstance('marc21');
        $q = "insert into search (titleid,colname,what) values (?,?,?)";
        $this->insert = $this->db->prepare($q);
        $this->isbd = new isbdElements($db);
    }

    function insert($titleid) {

        $this->isbd->getAllTags($titleid);

        $ti = $this->isbd->title();
        if ($ti !== '') {
            $this->db->query($this->insert, [$titleid, 'title', $ti]);
        }
        $ti = $this->isbd->author();
        if ($ti !== '') {
            $this->db->query($this->insert, [$titleid, 'autor', $ti]);
        }
        $ti = $this->isbd->verlag();
        if ($ti !== '') {
            $this->db->query($this->insert, [$titleid, 'verlag', $ti]);
        }
    }

    function rebuild() {
        $this->db->query("delete from search");

        $q = "select id from titles";
        $rows = $this->db->query($q);

        foreach ($rows as $row) {
            $this->insert($row->id);
        }
    }
}
