<?php

class Logger {
    private $logDir;
    private $logLevel;
    private $maxFileSize = 10485760;
    private $maxFiles = 5;

    const LEVEL_DEBUG = 0;
    const LEVEL_INFO = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 3;

    public function __construct($level = self::LEVEL_INFO) {
        $this->logDir = DATA_ROOT . '/logs';
        $this->logLevel = $level;
        if (!file_exists($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
    }

    public function debug($message, $context = []) {
        $this->log(self::LEVEL_DEBUG, $message, $context);
    }

    public function info($message, $context = []) {
        $this->log(self::LEVEL_INFO, $message, $context);
    }

    public function warning($message, $context = []) {
        $this->log(self::LEVEL_WARNING, $message, $context);
    }

    public function error($message, $context = []) {
        $this->log(self::LEVEL_ERROR, $message, $context);
    }

    public function log($level, $message, $context = []) {
        if ($level < $this->logLevel) {
            return;
        }

        $levelNames = ['DEBUG', 'INFO', 'WARNING', 'ERROR'];
        $levelName = $levelNames[$level];
        $timestamp = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'unknown';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'unknown';

        $logEntry = [
            'timestamp' => $timestamp,
            'level' => $levelName,
            'message' => $message,
            'context' => $context,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'request_uri' => $requestUri,
            'method' => $method
        ];

        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        
        $this->writeLog($logLine);
    }

    public function recordAction($action, $target = null, $result = 'success') {
        $userId = $this->getCurrentUserId();
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'target' => $target,
            'result' => $result,
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        $filePath = $this->logDir . '/actions.log';
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        
        file_put_contents($filePath, $logLine, FILE_APPEND);
        $this->rotateLog('actions');
    }

    public function recordAccess($code, $action = 'download') {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'code' => $code,
            'action' => $action,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];

        $filePath = $this->logDir . '/access.log';
        $logLine = json_encode($logEntry, JSON_UNESCAPED_UNICODE) . "\n";
        
        file_put_contents($filePath, $logLine, FILE_APPEND);
        $this->rotateLog('access');
    }

    public function getLogs($type = 'general', $limit = 100) {
        $filePath = $this->logDir . '/' . ($type === 'actions' ? 'actions' : ($type === 'access' ? 'access' : 'app')) . '.log';
        
        if (!file_exists($filePath)) {
            return [];
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        $lines = array_reverse($lines);
        $result = [];

        foreach ($lines as $line) {
            if (count($result) >= $limit) {
                break;
            }
            $entry = json_decode($line, true);
            if ($entry) {
                $result[] = $entry;
            }
        }

        return $result;
    }

    public function getActionLogs($limit = 100) {
        return $this->getLogs('actions', $limit);
    }

    public function getAccessLogs($limit = 100) {
        return $this->getLogs('access', $limit);
    }

    public function getAppLogs($limit = 100) {
        return $this->getLogs('general', $limit);
    }

    public function clearLogs($type = 'all') {
        if ($type === 'all' || $type === 'actions') {
            $this->clearLogFile('actions');
        }
        if ($type === 'all' || $type === 'access') {
            $this->clearLogFile('access');
        }
        if ($type === 'all' || $type === 'app') {
            $this->clearLogFile('app');
        }
    }

    private function writeLog($logLine) {
        $filePath = $this->logDir . '/app.log';
        file_put_contents($filePath, $logLine, FILE_APPEND);
        $this->rotateLog('app');
    }

    private function rotateLog($prefix) {
        $filePath = $this->logDir . "/{$prefix}.log";
        
        if (file_exists($filePath) && filesize($filePath) > $this->maxFileSize) {
            for ($i = $this->maxFiles - 1; $i >= 0; $i--) {
                $oldFile = $filePath . ($i > 0 ? ".{$i}" : '');
                $newFile = $filePath . '.' . ($i + 1);
                if (file_exists($oldFile)) {
                    rename($oldFile, $newFile);
                }
            }
            file_put_contents($filePath, '');
        }
    }

    private function clearLogFile($prefix) {
        $filePath = $this->logDir . "/{$prefix}.log";
        if (file_exists($filePath)) {
            file_put_contents($filePath, '');
        }
        for ($i = 1; $i <= $this->maxFiles; $i++) {
            $oldFile = $filePath . ".{$i}";
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
    }

    private function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}
