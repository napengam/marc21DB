<?php

/* @author Heinz
 */

trait httpRequest {

    public $param;

    function readRequest() {
        $json = file_get_contents('php://input');
        if ($json == '') {
            exit;
        }
        $this->param = (object) json_decode($json, true);
        $this->param->error = '';
        $this->param->result = '';
        return $this->param;
    }

    function closeRequest($param) {
        return json_encode($param, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    }
}
