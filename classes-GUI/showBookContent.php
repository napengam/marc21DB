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

        $out = file_get_contents($href);

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
}

$xx = new showBookContent();
