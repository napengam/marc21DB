<?php

spl_autoload_register(function ($class) {
// Define the top-level namespace directory
    $toplevel = 'marc21DB';
    $dir = str_replace('\\', '/', __DIR__) . '/';   
    $arr = explode($toplevel, $dir);
    $dir = $arr[0] . $toplevel;

    $dirs = [];
    $dirs[] = $dir . '/classes/';
    $dirs[] = $dir . '/classes-get21/';
    $dirs[] = $dir . '/classes-GUI/';
    $dirs[] = $dir . '/classes-Hooks/';

    $class_path = str_replace('\\', '/', $class);
    foreach ($dirs as $dir) {
        $file = $dir . $class_path . '.php';
        if (file_exists($file)) {
            include $file;
            return;
        }
    }
});
