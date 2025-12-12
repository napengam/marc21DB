<?php

require '../include/core.inc.php';

class showBookContent {

    use httpRequest;

    function __construct() {


        $href = $_GET['href'];
        $ti = $_GET['ti'];
        /*
         * ***********************************************
         * create page
         * **********************************************
         */
        $out = $this->fetch_url($href);

        if (strpos($out, '<html>')) {
            echo $out;
            return;
        }


        $pg = new page();
        $pg->docTypeEtal();

        $pg->sectionTitel($ti);
        $pg->container();
        echo $out;
        $pg->closeContainer();
        $pg->closeSection();
        $pg->closePage();
    }

    function fetch_url($url) {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false, // disable cert verification
            CURLOPT_SSL_VERIFYHOST => false, // disable hostname check
            CURLOPT_USERAGENT => "Mozilla/5.0",
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);

        $data = curl_exec($ch);

        if ($data === false) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        curl_close($ch);
        return $data;
    }
}

$xx = new showBookContent();
