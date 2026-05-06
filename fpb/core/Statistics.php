<?php

class Statistics {
    private $storageDir;

    public function __construct() {
        $this->storageDir = DATA_ROOT . '/stats';
        if (!file_exists($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    public function recordUpload($fileCode) {
        $today = date('Y-m-d');
        $stats = $this->getDailyStats($today);
        $stats['uploads']++;
        $stats['total_size'] += $fileCode->size;
        $this->saveDailyStats($today, $stats);
        $this->updateTotalStats('uploads', 1);
        $this->updateTotalStats('total_size', $fileCode->size);
    }

    public function recordDownload($fileCode) {
        $today = date('Y-m-d');
        $stats = $this->getDailyStats($today);
        $stats['downloads']++;
        $this->saveDailyStats($today, $stats);
        $this->updateTotalStats('downloads', 1);
    }

    public function getDailyStats($date) {
        $filePath = $this->getDailyFilePath($date);
        if (file_exists($filePath)) {
            return json_decode(file_get_contents($filePath), true) ?: $this->getDefaultStats();
        }
        return $this->getDefaultStats();
    }

    public function getTotalStats() {
        $filePath = $this->storageDir . '/total.json';
        if (file_exists($filePath)) {
            return json_decode(file_get_contents($filePath), true) ?: $this->getDefaultTotal();
        }
        return $this->getDefaultTotal();
    }

    public function getRecentStats($days = 7) {
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i day"));
            $stats = $this->getDailyStats($date);
            $result[] = [
                'date' => $date,
                'uploads' => $stats['uploads'],
                'downloads' => $stats['downloads'],
                'size' => $stats['total_size']
            ];
        }
        return $result;
    }

    public function getStorageUsage() {
        $shareDir = DATA_ROOT . '/share';
        $totalSize = 0;
        
        if (file_exists($shareDir)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($shareDir));
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $totalSize += $file->getSize();
                }
            }
        }

        return $totalSize;
    }

    public function getFileStatistics() {
        require_once __DIR__ . '/../models/FileCodes.php';
        
        $total = FileCodes::count();
        $today = date('Y-m-d');
        $todayCount = 0;
        $totalSize = 0;
        $expiredCount = 0;
        $textCount = 0;
        $fileCount = 0;

        $files = FileCodes::all();
        foreach ($files as $file) {
            $totalSize += $file->size;
            if (strpos($file->created_at, $today) === 0) {
                $todayCount++;
            }
            if ($file->isExpired()) {
                $expiredCount++;
            }
            if ($file->text) {
                $textCount++;
            } else {
                $fileCount++;
            }
        }

        return [
            'total_files' => $total,
            'today_uploads' => $todayCount,
            'total_size' => $totalSize,
            'expired_files' => $expiredCount,
            'text_count' => $textCount,
            'file_count' => $fileCount,
            'storage_used' => $this->getStorageUsage()
        ];
    }

    public function getStorageWarning($threshold = 90) {
        $usage = $this->getStorageUsage();
        $diskFree = disk_free_space(DATA_ROOT);
        $diskTotal = disk_total_space(DATA_ROOT);
        $usagePercent = ($diskTotal - $diskFree) / $diskTotal * 100;
        
        return [
            'used_percent' => round($usagePercent, 2),
            'used_bytes' => $diskTotal - $diskFree,
            'free_bytes' => $diskFree,
            'total_bytes' => $diskTotal,
            'is_warning' => $usagePercent >= $threshold,
            'formatted_used' => $this->formatBytes($diskTotal - $diskFree),
            'formatted_free' => $this->formatBytes($diskFree),
            'formatted_total' => $this->formatBytes($diskTotal)
        ];
    }

    private function saveDailyStats($date, $stats) {
        $filePath = $this->getDailyFilePath($date);
        file_put_contents($filePath, json_encode($stats));
    }

    private function updateTotalStats($key, $value) {
        $filePath = $this->storageDir . '/total.json';
        $stats = $this->getTotalStats();
        $stats[$key] = ($stats[$key] ?? 0) + $value;
        file_put_contents($filePath, json_encode($stats));
    }

    private function getDailyFilePath($date) {
        return $this->storageDir . "/{$date}.json";
    }

    private function getDefaultStats() {
        return [
            'uploads' => 0,
            'downloads' => 0,
            'total_size' => 0
        ];
    }

    private function getDefaultTotal() {
        return [
            'uploads' => 0,
            'downloads' => 0,
            'total_size' => 0
        ];
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
