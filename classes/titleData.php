<?php

require '../include/connect.inc.php';
require 'tags2mem.php';

class titleData {

    private $tm, $param;

    function __construct($db, $param) {
        $this->tm = new isbdElements($db);
        $this->param = $param;
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
        $ti = $this->lt($tm->title());
        $ti = $this->yellow($this->param, 'title', $ti);
        /*
         * ***********************************************
         * autor
         * **********************************************
         */

        $au = $this->lt($tm->author());
        $au = $this->yellow($this->param, 'autor', $au);

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
        $tmn = $tm->isbn();
        $tmn .= " " . $tm->price();
        $tmn = $this->lt($tmn);

        /*
         * ***********************************************
         * DDC
         * **********************************************
         */
        $ddc = $this->lt($tm->ddc());

        /*
         * ***********************************************
         * Verlagsort
         * **********************************************
         */
        $vo = $this->lt($tm->ort());
        /*
         * ***********************************************
         * Verlag
         * **********************************************
         */
        $vl = $this->lt($tm->verlag());
        $vl = $this->yellow($this->param, 'verlag', $vl);

        /*
         * ***********************************************
         * physical description
         * **********************************************
         */
        $dc = $this->lt($tm->physical());

        /*
         * ***********************************************
         * table of content index etc
         * **********************************************
         */

        $out = $tm->indexEtAl();

        $ix = '';
        foreach ($out as $o) {
            $x = $o['x'];
            $href = $o['h'];
            if ($x) {
                $pu = parse_url($href);
                if (strpos($pu['host'], 'deposit') !== false) {
                    $ix .= "$x  <a href='$href' target='nn' data-what='$x'  data-funame='marc21DB.showBookContent'> <i class='fa-solid fa-bars'></i></a> ";
                } else {
                    $ix .= "$x  <a href='$href' target='nn' > <i class='fa-solid fa-bars'></i></a> ";
                }
            }
        }


        /*
         * **********************************************
         * assemble title
         * **********************************************
         */

        $ti = "<span class='theTitle'> $ti  $dnb</span>";

        if (trim($au)) {
            $au = "<br>$au";
        }

        if (trim($tmn)) {
            $tmn = "<br>$tmn";
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
        /*
         * ***********************************************
         * series
         * **********************************************
         */
        $syw = $tm->serie();

        $sep = ';';
        if (trim("$au$vo$vl") == '' && $dc === '') {
            $sep = '';
        }


        $title = "title='Alle Tags für diesen Titel zeigen'";
        $topLine = $this->level($titleid, $ddc, $syw);
        $out = "$topLine<b>$ti</b>$au$vo $vl$sep $dc$tmn$ix";

        return $out;
    }

    function yellow($param, $name, $line) {

        $words = $param->search;
        if (trim($param->search) == '' || $param->colname != $name) {
            return $line;
        }
        $p = '/\b' . preg_replace('/\s+/', '|\b', $words) . "/iu";
        $s = "<span style='background-color:yellow'>" . '$0' . "</span>";
        return preg_replace($p, $s, $line);
    }

    function lt($s) {

        return preg_replace('/</', '&lt;', $s);
    }

    function level($id, $ddc, $syw) {
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
                <div class='level-item'>
                    <p>/ $syw</p> 
                </div>
            </div>
           <!-- Right side -->
            <div id='levright' class='level-right is-hidden'>
            <i  data-id='$id'  data-funame='marc21DB.showRaw'  class='  is-clickable fa-solid fa-magnifying-glass-plus' ></i>
            </div>
        </nav>
        ";
        return $out;
    }
}
