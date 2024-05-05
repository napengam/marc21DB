<?php

class sftpDownLoad {

    public $error = '';

    function download($ftpFileDir, $file) {

        if (file_exists($ftpFileDir . $file)) {
            return $ftpFileDir . $file;
        }
       
        $content = file_get_contents("https://data.dnb.de/DNBlfdMarc21/$file");
        if (!$content) {
            return false;
        }
        $f = fopen($ftpFileDir . $file, 'w');
        if (!$f) {
            return false;
        }
        fwrite($f, $content, strlen($content));
        fclose($f);
        return $ftpFileDir . $file;
    }
}
