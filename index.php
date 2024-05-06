<?php

require 'include/connect.inc.php';
require 'include/core.inc.php';
require 'include/adressPort.inc.php';

// Generate a CSRF token
$csfr = bin2hex(random_bytes(32));

// Store the token in the session
session_name('marc21DB');
session_start();
foreach ($_SESSION AS $k => $v) {
    unset($_SESSION[$k]);
}
$_SESSION['csfr'] = $csfr;

$pg = new page();

$pg->docTypeEtal('DNB Neuerscheinungen',
        "#ddctable tr{cursor:pointer} .scroller{overflow-y: scroll;max-height:500px;
  scrollbar-color: blue white;scrollbar-width: thin;
  scrollbar-width: thin;}");
$pg->header('DNB Neuerscheinungen');
$pg->container();
//$pg->navBar();
echo "<p>&nbsp;";
$pg->closeContainer();
$pg->container();

echo '<div class="columns">  
  <div id="titles" class="column is-8 ">Titel</div>
  <div id="ddc" class="column is-4 is-size-7 ">DDC-Facette</div> 
  <div class="columns">
</div>';

$pg->closeContainer();

echo
"<script>
var server='$Address';
var csfr='$csfr';    
</script>";
?>
<script src="js/toolTip.js"></script>
<script src="js/socketWebClient.js"></script>
<script src="js/contextMenu.js"></script>
<script src="js/stickyCSS.js"></script>
<script src="js/sortTable.js"></script>
<script src="js/bulmaDialog.js"></script>
<script src="js/myBackend.js"></script>
<script src="js21/marc21DB.js"></script>

<?php

$pg->footer();
$pg->closePage();

