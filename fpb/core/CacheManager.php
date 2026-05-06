<?php

class CacheManager {
    private $cacheDir;
    private $defaultTtl = 3600;

    public function __construct() {
        $this->cacheDir = DATA_ROOT . '/cache';
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get($key) {
        $filePath = $this->getCacheFilePath($key);
        
        if (!file_exists($filePath)) {
            return null;
        }

        $data = json_decode(file_get_contents($filePath), true);
        
        if (!$data || !isset($data['expire']) || $data['expire'] < time()) {
            $this->delete($key);
            return null;
        }

        return $data['value'];
    }

    public function set($key, $value, $ttl = null) {
        $ttl = $ttl ?? $this->defaultTtl;
        $filePath = $this->getCacheFilePath($key);
        
        $data = [
            'value' => $value,
            'expire' => time() + $ttl,
            'created_at' => time()
        ];

        file_put_contents($filePath, json_encode($data));
    }

    public function delete($key) {
        $filePath = $this->getCacheFilePath($key);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function has($key) {
        $filePath = $this->getCacheFilePath($key);
        if (!file_exists($filePath)) {
            return false;
        }

        $data = json_decode(file_get_contents($filePath), true);
        return $data && isset($data['expire']) && $data['expire'] >= time();
    }

    public function clear() {
        $files = glob($this->cacheDir . '/*.json');
        if ($files) {
            foreach ($files as $file) {
                unlink($file);
            }
        }
    }

    public function clearExpired() {
        $files = glob($this->cacheDir . '/*.json');
        if ($files) {
            foreach ($files as $file) {
                $data = json_decode(file_get_contents($file), true);
                if ($data && isset($data['expire']) && $data['expire'] < time()) {
                    unlink($file);
                }
            }
        }
    }

    public function getKeys() {
        $files = glob($this->cacheDir . '/*.json');
        $keys = [];
        foreach ($files as $file) {
            $keys[] = basename($file, '.json');
        }
        return $keys;
    }

    public function getStats() {
        $files = glob($this->cacheDir . '/*.json');
        $total = count($files);
        $size = 0;
        
        foreach ($files as $file) {
            $size += filesize($file);
        }

        return [
            'count' => $total,
            'size' => $size,
            'formatted_size' => $this->formatBytes($size)
        ];
    }

    private function getCacheFilePath($key) {
        return $this->cacheDir . '/' . md5($key) . '.json';
    }

    private function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

class ConfigCache {
    private static $cache;
    private static $lastReload = 0;
    private static $reloadInterval = 300;

    public static function init() {
        if (!self::$cache) {
            self::$cache = new CacheManager();
        }
    }

    public static function get($key, $default = null) {
        self::init();
        
        $cacheKey = 'config_' . $key;
        
        if (self::$cache->has($cacheKey)) {
            return self::$cache->get($cacheKey);
        }

        global $settings;
        $value = $settings[$key] ?? $default;
        
        if ($value !== null) {
            self::$cache->set($cacheKey, $value, self::$reloadInterval);
        }

        return $value;
    }

    public static function reload() {
        self::init();
        self::$cache->clear();
        self::$lastReload = time();
    }

    public static function isExpired() {
        return time() - self::$lastReload > self::$reloadInterval;
    }
}
