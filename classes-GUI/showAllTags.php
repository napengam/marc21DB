<?php

require_once '../include/connect.inc.php';
require '../include/core.inc.php';

class showAllTags {

    use httpRequest;

    function __construct() {
        global $connect_pdo;

        $titleid = $_GET['titleid'];
        $ta = new tags2mem($connect_pdo);
        $tags = $ta->setTags($titleid);
        /*
         * ***********************************************
         * create page
         * **********************************************
         */
        $pg = new page();
        $pg->docTypeEtal();

        $out = [];
        $out[] = "<table id='alltags' class='table is-narrow is-bordered is-striped is-size-7'>"
                . "<thead style='background-color:white'>"
                . "<tr><th>Tag</th><th>Seq</th><th>Indicator</th><th>Code</th><th>Data</th></tr>"
                . "</thead>";
        foreach ($tags as $tag) {
            $out[] = "<tr>";
            foreach ($tag as $k => $v) {
                if ($k == 'consumed') {
                    continue;
                }
                if ($k === 'indicator') { 
                    $v = preg_replace('/\s+/', '_', $v);
                    if ($v === '__') {
                        $v = '';
                    }
                }
                $out[] = "<td>$v</td>";
            }
            $out[] = "</tr>";
        }
        $out[] = "</table>";
        echo implode('', $out);
        ?>
        <script src='../js/stickyCSS.js'></script>
        <script>
            makeSticky('alltags');
            !function () {
                var obj = document.getElementById('alltags'), uu;
                [].forEach.call(obj.rows, function (r) {
                    let u = r.cells[4].innerHTML;
                    uu = u;
                    if (u.indexOf('(uri)') === 0) {
                        u = u.split('(uri)')[1];
                    }
                    if (u.indexOf('http') === 0) {
                        r.cells[4].innerHTML = '<a href="' + u + '" target=_new>' + uu + '</a>';
                    }
                    if (r.cells[0].innerHTML === '001') {
                        r.cells[4].innerHTML = '<a target=dnbinfo title="Titel in der DNB" href="http://d-nb.info/' + r.cells[4].innerHTML + '">' + r.cells[4].innerHTML + '</a>';
                    }
                });
            }();
        </script>

        <?php

        $pg->closePage();
    }
}

$xx = new showAllTags();
