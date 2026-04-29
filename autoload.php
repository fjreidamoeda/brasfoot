<?php
spl_autoload_register(function($class) {
    $paths = [
        __DIR__ . '/classes/' . $class . '.php',
        __DIR__ . '/classes/' . $class . '.class.php'
    ];
    
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
