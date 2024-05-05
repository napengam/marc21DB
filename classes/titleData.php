<?php

require '../include/connect.inc.php';
require 'tags2mem.php';

class titleData {

    private $tm;

    function __construct($db) {
        $this->tm = new tags2mem($db);
    }

    function makeISBD($titleid) {

        $tm = $this->tm;
        $tm->setTags($titleid);

        /*
         * ***********************************************
         * dnb info
         * **********************************************
         */
        $title = "title='Titel in der DNB anzeigen'";
        $info = $tm->getData('001', 1, '');
        $dnb = "<span ><a href='http://d-nb.info/$info' target='nn'><i  $title class='fa-solid fa-book'></i></a></span>";

        /*
         * ***********************************************
         * Titel und zusatz
         * **********************************************
         */
        $tia = [];
        $tia[] = $tm->getData('245', 1, 'a');
        $tia[] = $tm->getData('245', 1, 'b');

        $ti = "<span class='theTitle'>" . implode(' / ', array_filter($tia)) . " $dnb</span>";
        /*
         * ***********************************************
         * autor
         * **********************************************
         */

        $au = $tm->getData('100', 1, 'a');
        $au .= " " . $tm->getData('100', 1, 'd');
        $au .= " " . $tm->getData('100', 1, 'e');

        $href = '';
        $x = $tm->getData('100', 1, '0');
        while ($x !== null) {
            if (substr($x, 0, 4) === 'http') {
                $href = $x;
                break;
            }
            $x = $tm->getData('100', 1, '0');
        }
        if ($href) {
            $title = "title='Informationen zum Autor in der DNB anzeigen'";
            $au = "<a href='$href' $title target='nn'>$au</a>";
        }


        /*
         * ***********************************************
         * ISBN Price
         * **********************************************
         */
        $isbn = $tm->getData('020', 1, '9');
        $isbn .= " " . $tm->getData('020', 1, 'c');

        /*
         * ***********************************************
         * DDC
         * **********************************************
         */
        $ddc = '';
        $q = $tm->getData('082', 1, 'q');
        $q2 = $tm->getData('082', 1, '2');
        if ($q == 'DE-101' && mb_substr($q2, 2) == 'sdnb') {
            $ddc = $tm->getData('082', 1, 'a');
        }
        if ($ddc == '') {
            $q = $tm->getData('083', 1, 'q');
            $q2 = $tm->getData('083', 1, '2');
            if ($q == 'DE-101' && mb_substr($q2, 2) == 'sdnb') {
                $ddc = $tm->getData('083', 1, 'a');
            }
            $q = $tm->getData('083', 2, 'q');
            $q2 = $tm->getData('083', 2, '2');
            if ($q == 'DE-101' && mb_substr($q2, 2) == 'sdnb') {
                $ddc .= " " . $tm->getData('083', 2, 'a');
            }
        }

        /*
         * ***********************************************
         * Verlagsort
         * **********************************************
         */
        $vo = $c = '';
        $x = $tm->getData('264', 1, 'a');
        while ($x !== null) {
            $vo .= $c . $x;
            $c = ', ';
            $x = $tm->getData('264', 1, 'a');
        }
        /*
         * ***********************************************
         * Verlag
         * **********************************************
         */
        $vl = $c = '';
        $x = $tm->getData('264', 1, 'b');
        while ($x !== null) {
            $vl .= $c . $x;
            $c = ', ';
            $x = $tm->getData('264', 1, 'b');
        }

        /*
         * ***********************************************
         * physical description
         * **********************************************
         */
        $dc = $c = '';
        $x = $tm->getData('300', 1, '');
        while ($x !== null) {
            $dc .= $c . $x;
            $c = ', ';
            $x = $tm->getData('300', 1, '');
        }

        /*
         * ***********************************************
         * table of content index
         * **********************************************
         */

        $ix = '';
        $x = $tm->getData('856', 1, '3');
        if ($x === 'Inhaltstext' || $x === 'Inhaltsverzeichnis') {
            $href = $tm->getData('856', 1, 'u');
            $ix .= "$x <a href='$href' target='nn' data-what='$x' onclick='marc21DB.showBookContent(this)'><i class='fa-solid fa-bars'></i></a> ";
        }
        $x = $tm->getData('856', 2, '3');
        if ($x === 'Inhaltstext' || $x === 'Inhaltsverzeichnis') {
            $href = $tm->getData('856', 2, 'u');
            $ix .= "$x  <a href='$href' target='nn' data-what='$x'  onclick='marc21DB.showBookContent(this)'> <i class='fa-solid fa-bars'></i></a> ";
        }

        /*
         * ***********************************************
         * assemble title
         * **********************************************
         */

        if (trim($au)) {
            $au = "<br>$au";
        }

        if (trim($isbn)) {
            $isbn = "<br>$isbn";
        }
        if (trim($ix)) {
            $ix = "<br>$ix";
        }
        if (trim($vo)) {
            $vo = "<br>$vo";
        }
        if (trim($vl)) {
            $vl = " ; $vl";
        }
        $title = "title='Alle Tags für diesen Titel zeigen'";
        $topLine = $this->level($titleid, $ddc);
        $out = "$topLine<b>$ti</b>$au$vo $vl $dc$isbn$ix";

        return $out;
    }

    function level($id, $ddc) {
        $out = "
        <!-- Main container -->
        <nav class='level'>
            <!-- Left side -->
            <div class='level-left'>
                <div class='level-item'>
                    <p>$id</p>
                </div>
                <div class='level-item'>
                    <p>/ $ddc</p> 
                </div>
            </div>
           <!-- Right side -->
            <div id='levright' class='level-right is-hidden'>
            <i  data-id='$id'  onclick='marc21DB.showRaw(this)'  class='  is-clickable fa-solid fa-magnifying-glass-plus' ></i>
            </div>
        </nav>
        ";
        return $out;
    }
}
