<?php

require '../include/connect.inc.php';
require '../include/core.inc.php';

$ins=new insertSearch($connect_pdo);
$ins->rebuild();