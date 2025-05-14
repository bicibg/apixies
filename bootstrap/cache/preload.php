<?php
require __DIR__ . '/../../vendor/autoload.php';
$map = require __DIR__ . '/../../vendor/composer/autoload_classmap.php';
foreach (array_keys($map) as $class) {
    @opcache_compile_file($map[$class]);
}

$files = [
    __DIR__ . '/../../app/Models',
    __DIR__ . '/../../app/Http/Controllers',
    __DIR__ . '/../../app/Services',
];

foreach ($files as $dir) {
    if (! is_dir($dir)) continue;
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );
    foreach ($it as $file) {
        if ($file->getExtension() === 'php') {
            @opcache_compile_file($file->getPathname());
        }
    }
}
