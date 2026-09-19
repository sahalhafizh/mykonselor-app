<?php

namespace App\Support;

class BuildAssets
{
    public static function isComplete(string $directory): bool
    {
        try {
            $base = realpath($directory);
            if ($base === false || ! is_file($base.'/manifest.json')) {
                return false;
            }

            $manifest = json_decode(file_get_contents($base.'/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($manifest) || ! isset($manifest['resources/css/app.css'], $manifest['resources/js/app.js'])) {
                return false;
            }

            foreach ($manifest as $entry) {
                if (! is_array($entry) || ! is_string($entry['file'] ?? null)) {
                    return false;
                }
                $path = realpath($base.'/'.$entry['file']);
                if ($path === false || ! str_starts_with($path, $base.DIRECTORY_SEPARATOR) || ! is_file($path) || filesize($path) === 0) {
                    return false;
                }
                foreach (['imports', 'dynamicImports'] as $kind) {
                    if (! is_array($entry[$kind] ?? [])) {
                        return false;
                    }
                    foreach ($entry[$kind] ?? [] as $import) {
                        if (! is_string($import) || ! isset($manifest[$import])) {
                            return false;
                        }
                    }
                }
            }

            return true;
        } catch (\Throwable $exception) {
            return false;
        }
    }
}
