<?php

class getOptions {

    public $in;

    /*
     * ***********************************************
     * we $expect an array of parameter names in the form
     * ['-p','-s','-w' .... ]
     * **********************************************
     */

    function __construct($expect) {
        $in = $default = [];
        foreach ($expect as $p) {
            $default[mb_substr($p, 1)] = '';
        }
        $in = $this->getOptArgv($expect);
        foreach ($default as $k => $v) {
            if (isset($in[$k])) {
                continue;
            }
            $in[$k] = '';
        }
        $this->in = $in;
        return;
    }

    function getOptArgv($expect) {
        global $argv, $argc;
        $out = [];
        for ($i = 1; $i < $argc; $i++) {
            if (array_search($argv[$i], $expect) === false) {
                continue;
            }
            $exp = mb_substr($argv[$i], 1);
            if ($i + 1 < $argc && mb_substr($argv[$i + 1], 0, 1) !== '-') {
                $i++;
                $out[$exp] = $argv[$i]; //parameter is given with value like '-w 24'
            } else {
                $out[$exp] = '1'; // parameter is given with no value
            }
        }
       
        return $out;
    }
}
