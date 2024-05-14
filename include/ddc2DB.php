<?php

require "connect.inc.php";
require "ddc_de.php";
require "ddc_en.php";
$q = "insert into ddc (ddc,isolang,descript)values (?,'de',?)";
$ddcins = $connect_pdo->prepare($q);
foreach ($ddc_de as $code => $descript) {
      $ddcins->execute([$code, $descript]);   
}
$q = "insert into ddc (ddc,isolang,descript)values (?,'en',?)";
$ddcins = $connect_pdo->prepare($q);
foreach ($ddc_en as $code => $descript) {
      $ddcins->execute([$code, $descript]);   
}
