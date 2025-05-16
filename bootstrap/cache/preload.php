<?php
// Skip preloading in CLI context to avoid warnings during artisan commands
if (php_sapi_name() === 'cli') {
    return;
}

// Otherwise proceed with preloading for web requests
try {
    require __DIR__ . '/../../vendor/autoload.php';

    // First, load core framework classes that many others depend on
    $priorityClasses = [
        // Add core Laravel classes here that others depend on
        Illuminate\Support\ServiceProvider::class,
        Illuminate\Foundation\Application::class,
        // Add more as needed
    ];

    foreach ($priorityClasses as $class) {
        if (class_exists($class)) {
            continue; // Skip if already loaded
        }

        try {
            class_exists($class);
        } catch (\Throwable $e) {
            // Log but continue
            error_log("Failed to preload priority class {$class}: " . $e->getMessage());
        }
    }

    // Then load the class map
    $map = require __DIR__ . '/../../vendor/composer/autoload_classmap.php';

    // Filter out problematic classes (customize this list based on your errors)
    $skipClasses = [
        'Psy\\Readline\\Hoa',
        'Carbon\\',
        'PHPUnit',
        'Symfony\\Component\\DependencyInjection',
        'Tests',
        'Nette\\',
    ];

    foreach ($map as $class => $path) {
        $skip = false;
        foreach ($skipClasses as $skipPattern) {
            if (str_starts_with($class, $skipPattern)) {
                $skip = true;
                break;
            }
        }

        if (!$skip) {
            try {
                @opcache_compile_file($path);
            } catch (\Throwable $e) {
                // Silently continue on error
            }
        }
    }

    // Finally, load application files
    $appDirectories = [
        __DIR__ . '/../../app/Models',
        __DIR__ . '/../../app/Http/Controllers',
        __DIR__ . '/../../app/Services',
    ];

    foreach ($appDirectories as $dir) {
        if (!is_dir($dir)) continue;

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir)
        );

        foreach ($it as $file) {
            if ($file->getExtension() === 'php') {
                try {
                    @opcache_compile_file($file->getPathname());
                } catch (\Throwable $e) {
                    // Silently continue on error
                }
            }
        }
    }
} catch (\Throwable $e) {
    // Log but don't crash
    error_log("Preload script error: " . $e->getMessage());
}
