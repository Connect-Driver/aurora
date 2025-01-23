<?php

spl_autoload_register(function (string $className) {
    $baseDir = __DIR__ . '/src/';
    
    $filePath = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
    
    if (file_exists($filePath)) {
        require $filePath;
    }
});
