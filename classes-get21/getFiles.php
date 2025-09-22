<?php

require '../include/core.inc.php';

$opt = new getOptions(['-y', '-w', '-s', '-f']);

/*
 * ***********************************************
 * merge default values if parameter not set
 * **********************************************
 */

$params = (object) $opt->in;
$default = (object) ['y' => date('y'), 'w' => date('W'), 's' => 'AB'];
foreach ($default as $k => $v) {
    if ($params->{$k}) {
        continue;
    }
    $params->{$k} = $v;
}

$y = isValid($params->y);
if ($y === null || $y < 0 || $y > intval(date('y'))) {
    exit;
}
$w = isValid($params->w);
if ($w === null || $w < 1 || $w > 52) {
    exit;
}
$w = sprintf("%02d", $w);
$y = sprintf("%02d", $y);
$s = $params->s;

if (($y && $w && $s) === false) {
    exit;
}

$serie = mb_str_split($s);
$sftp = new sftpDownload();
$m21 = new marc21toDB();
$h = new m21HooksDB();

foreach ($serie as $s1) {
    $m21file = "{$s1}{$y}{$w}utf8.mrc";

    if ($m21->fileExists('../mrc/' . $m21file) > 0) {
        if ($params->f != '1') {
            echo "*** File $m21file allready loaded \r\n ***";
            continue;
        }
        $m21->fileDelete('../mrc/' . $m21file);
    }
    echo "*** Reading file $m21file *** \r\n";
    $file = $sftp->download('../mrc/', $m21file);
    if ($file !== false && $m21->readFile($file)) {
        $m21->setHooks($h);
        $m21->insertTitles();
    }
}

function isValid($str) {
// Check if the string has length 2 and consists entirely of digits
    if (strlen($str) === 2 && ctype_digit($str)) {
// Convert the string to an integer
        return intval($str);
    }
    return null; // Invalid hour
}
