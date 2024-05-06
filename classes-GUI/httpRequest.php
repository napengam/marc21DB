<?php

/* @author Heinz
 */

trait httpRequest {

    public $param;

    function readRequest() {
        session_name('marc21DB');
        session_start();

        $this->param = (object) [];

        if (!isset($_SERVER['REQUEST_METHOD']) || strtolower($_SERVER['REQUEST_METHOD']) !== 'post') {
            $this->param->error = 'Security Violation !!';
            echo $this->closeRequest($this->param);
            exit;
        }

        $json = file_get_contents('php://input');
        if ($json == '') {
            $this->param->error = 'json empty';
            echo $this->closeRequest($this->param);
            exit;
        }
        $this->param = (object) json_decode($json, true);
        if (isset($this->param->csfr) && $this->param->csfr !== $_SESSION['csfr']) {
            $this->param->error = 'Security Violation !!';
            $this->param->result = '';
            echo $this->closeRequest($this->param);
            exit;
        }

        $this->param->error = '';
        $this->param->result = '';
        return $this->param;
    }

    function closeRequest($param) {
        return json_encode($param, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    }
}
