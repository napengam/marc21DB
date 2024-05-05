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
        $pg = new page();
        $pg->docTypeEtal();

        $out = file_get_contents($href);
        $pg->sectionTitel($ti);
        $pg->container();
        echo $out;
        $pg->closeContainer();
        $pg->closeSection();
        $pg->closePage();
    }
}

$xx = new showBookContent();
