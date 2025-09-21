<?php

class GetAllConfig {

    private static $cache = [];

    public static function load($project = 'marc21DB') {
        if (empty($project)) {
            throw new RuntimeException("Missing project directory name");
        }

        // Return cached config if available
        if (isset(self::$cache[$project])) {
            return self::$cache[$project];
        }

        // Normalize directory path
        $dir = str_replace('\\', '/', __DIR__);
        $parts = explode("/$project", $dir);

        if (count($parts) < 2) {
            throw new RuntimeException("Unable to resolve project base path for: $project");
        }

        $basePath = $parts[0];
        $configPath = $basePath . "/$project/config/config.ini";

        if (!file_exists($configPath)) {
            throw new RuntimeException("Config file not found: $configPath");
        }

        $raw = file_get_contents($configPath);
        if ($raw === false) {
            throw new RuntimeException("Failed to read config file: $configPath");
        }

        $raw = str_replace('__DOCUMENT_ROOT__', $basePath, $raw);
        $url = self::getProjectUrl($project);
        $raw = str_replace('__URL__', $url, $raw);

        $parsed = parse_ini_string($raw, true, INI_SCANNER_TYPED);

        if ($parsed === false) {
            throw new RuntimeException("Invalid INI syntax in: $configPath");
        }

        $required = self::_getRequiredKeys();

        foreach ($required as $section => $keys) {
            if (!isset($parsed[$section])) {
                throw new RuntimeException("Missing required section: [$section]");
            }

            foreach ($keys as $key => $default) {
                if (!array_key_exists($key, $parsed[$section])) {
                    if ($default === null) {
                        throw new RuntimeException("Missing required config key: [$section] $key");
                    } else {
                        $parsed[$section][$key] = $default;
                    }
                }
            }
        }

        self::$cache[$project] = $parsed;
        return $parsed;
    }

    private static function getProjectUrl($project) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";

        $host = $_SERVER['HTTP_HOST'];         // e.g., www.example.com
        $requestUri = $_SERVER['REQUEST_URI']; // e.g., /path/page.php?param=value

        $url = $protocol . $host . $requestUri;
        $arr = explode("/$project/", $url);
        $url = $arr[0] . "/$project/";
        return $url;
    }

    private static function _getRequiredKeys() {
        return [
            'marc21' => [
                'host' => null,
                'dbname' => null,
                'user' => null,
                'password' => null,
            ]
        ];
    }
}
