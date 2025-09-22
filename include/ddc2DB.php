<?php

require_once "core.inc.php";
require "ddc_de.php";
require "ddc_en.php";
$db = PDODB::getInstance('marc21');
$q = "insert into ddc (ddc,isolang,descript)values (?,'de',?)";
$ddcins = $db->prepare($q);
foreach ($ddc_de as $code => $descript) {
    $bd->query($ddcins, [$code, $descript]);
}
$q = "insert into ddc (ddc,isolang,descript)values (?,'en',?)";
$ddcins = $db->prepare($q);
foreach ($ddc_en as $code => $descript) {
    $bd->query($ddcins, [$code, $descript]);
}
