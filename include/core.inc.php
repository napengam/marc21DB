<?php

spl_autoload_register(function ($class) {
// Define the top-level namespace directory
    $dir = str_replace('\\', '/', __DIR__) . '/';
    $dir = mb_substr($dir, 0, mb_strpos($dir, 'marc21DB', 0) + 8);

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
