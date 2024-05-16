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
        $this->db = $db;
        $q = "insert into search (titleid,colname,what) values (?,?,?)";
        $this->insert = $db->prepare($q);
        $this->isbd = new isbdElements($db);
    }

    function insert($titleid) {

        $this->isbd->getAllTags($titleid);

        $ti = $this->isbd->title();
        if ($ti !== '') {
            $this->insert->execute([$titleid, 'title', $ti]);
        }
        $ti = $this->isbd->author();
        if ($ti !== '') {
            $this->insert->execute([$titleid, 'autor', $ti]);
        }
        $ti = $this->isbd->verlag();
        if ($ti !== '') {
            $this->insert->execute([$titleid, 'verlag', $ti]);
        }
    }
}
