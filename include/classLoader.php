<?php

/**
 * Dynamically loads classes from specified directories.
 * Builds and updates class map when missing classes are found.
 * 
 * @requires explicit $paths input
 */
class ClassLoader {

    public static function load(string $projectFolder, array $paths): void {
        $baseDir = dirname(__DIR__);
        $basePath = self::findBasePath($baseDir, $projectFolder);

        if (!$basePath) {
            throw new Exception("Base path containing '{$projectFolder}' not found.");
        }

        $autoloadDir = $basePath . '/autoload';
        $classMapFile = $autoloadDir . '/classmap.php';

        if (!is_dir($autoloadDir)) {
            mkdir($autoloadDir, 0775, true);
        }

        // Load or create class map
        $classMap = file_exists($classMapFile) ? require $classMapFile : self::fillClassMapOnce($basePath, $paths, $classMapFile);

        // Register autoloader
        spl_autoload_register(function ($class) use ($basePath, $paths, &$classMap, $classMapFile) {

            $a = isset($classMap[$class]);
            $b = file_exists($classMap[$class]);
            if (isset($classMap[$class]) && file_exists($classMap[$class])) {
                require_once $classMap[$class];
                return;
            }

            $classMap = self::fillClassMapOnce($basePath, $paths, $classMapFile);
            if (isset($classMap[$class])) {
                require_once $classMap[$class];
                return;
            }
            throw new Exception("Class $class not found.");
        });
    }

    private static function fillClassMapOnce(string $basePath, array $paths, string $classMapFile): array {
        $classMap = [];

        foreach ($paths as $path) {
            $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("$basePath/$path"));
            foreach ($rii as $file) {
                if (!$file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }
                $defs = self::getDefinitions($file->getPathname());
                foreach ($defs as $def) {
                    $classMap[$def] = str_replace('\\', '/', $file->getPathname());
                }
            }
        }
        ksort($classMap);

        file_put_contents($classMapFile, "<?php\n\n" .
                "// Auto-generated class map. Do not edit manually.\n" .
                "// Generated on: " . date('Y-m-d H:i:s') . "\n\n" .
                "return " . var_export($classMap, true) . ";\n");

        return $classMap;
    }

    private static function getDefinitions(string $file): array {
        if (!is_file($file) || pathinfo($file, PATHINFO_EXTENSION) !== 'php') {
            return [];
        }

        $contents = file_get_contents($file);
        $tokens = token_get_all($contents);

        $definitions = [];
        $namespace = '';

        for ($i = 0; $i < count($tokens); $i++) {
            if (!is_array($tokens[$i])) {
                continue;
            }

            // Capture namespace
            if ($tokens[$i][0] === T_NAMESPACE) {
                $namespace = '';
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if (is_array($tokens[$j]) && ($tokens[$j][0] === T_STRING || $tokens[$j][0] === T_NAME_QUALIFIED)) {
                        $namespace .= $tokens[$j][1];
                    } elseif ($tokens[$j] === ';' || $tokens[$j] === '{') {
                        break;
                    }
                }
            }

            // Detect class/interface/trait
            if (in_array($tokens[$i][0], [T_CLASS, T_INTERFACE, T_TRAIT], true)) {
                // Skip anonymous classes
                if ($tokens[$i][0] === T_CLASS) {
                    $prev = $tokens[$i - 1] ?? null;
                    if (is_array($prev) && $prev[0] === T_NEW) {
                        continue;
                    }
                }

                // Next T_STRING is the name
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if (is_array($tokens[$j]) && $tokens[$j][0] === T_STRING) {
                        $name = $tokens[$j][1];
                        $definitions[] = ($namespace ? $namespace . '\\' : '') . $name;
                        break;
                    }
                }
            }
        }

        return $definitions;
    }

    private static function findBasePath(string $startPath, string $targetFolder): ?string {
        $parts = explode(DIRECTORY_SEPARATOR, $startPath);
        $path = [];

        foreach ($parts as $part) {
            $path[] = $part;
            if ($part === $targetFolder) {
                return implode(DIRECTORY_SEPARATOR, $path);
            }
        }

        return null;
    }
}

/*
 * ***********************************************
 * where to look below  for class files
 * **********************************************
 */

$paths = [
    '/classes/',
    '/classes-get21/',
    '/classes-GUI/',
    '/classes-Hooks/'
];

$projectDir = getFirstDirUnderDocroot();
define('PROJECT_DIR', $projectDir);

ClassLoader::load(PROJECT_DIR, $paths);

function getFirstDirUnderDocroot(): ?string {
    $docRoot = realpath($_SERVER['DOCUMENT_ROOT']);  
    $current = realpath(__DIR__);                   

    if (strpos($current, $docRoot) !== 0) {
        return null; // not under docroot
    }

    $relative = ltrim(str_replace($docRoot, '', $current), DIRECTORY_SEPARATOR);
    $parts = explode(DIRECTORY_SEPARATOR, $relative);

    return $parts[0] ?? null;  // first dir after docroot
}
