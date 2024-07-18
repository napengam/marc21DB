<?php

/* @author Heinz
 */

trait httpRequest {

    public $param;

    function readRequest() {

        $this->param = (object) [];

        if (!session_name('marc21DB') ||
                !session_start() ||
                !isset($_SERVER['REQUEST_METHOD']) ||
                $_SERVER['REQUEST_METHOD'] !== 'POST') {
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
        return json_encode($param); //, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    }
}
