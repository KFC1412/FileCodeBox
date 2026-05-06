<?php

class SessionManager {
    private $sessionDir;
    private $sessionExpire = 3600;

    public function __construct($expire = 3600) {
        $this->sessionDir = DATA_ROOT . '/sessions';
        $this->sessionExpire = $expire;
        if (!file_exists($this->sessionDir)) {
            mkdir($this->sessionDir, 0755, true);
        }
        $this->cleanExpiredSessions();
    }

    public function createSession($userId, $userType = 'admin') {
        $sessionId = $this->generateSessionId();
        $sessionData = [
            'session_id' => $sessionId,
            'user_id' => $userId,
            'user_type' => $userType,
            'created_at' => time(),
            'last_activity' => time()
        ];
        $filePath = $this->getSessionFilePath($sessionId);
        file_put_contents($filePath, json_encode($sessionData, JSON_PRETTY_PRINT));
        return $sessionId;
    }

    public function getSession($sessionId) {
        $filePath = $this->getSessionFilePath($sessionId);
        if (!file_exists($filePath)) {
            return null;
        }
        $data = json_decode(file_get_contents($filePath), true);
        if ($data && time() - $data['last_activity'] < $this->sessionExpire) {
            $data['last_activity'] = time();
            file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
            return $data;
        }
        $this->destroySession($sessionId);
        return null;
    }

    public function destroySession($sessionId) {
        $filePath = $this->getSessionFilePath($sessionId);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function isValidSession($sessionId) {
        return $this->getSession($sessionId) !== null;
    }

    public function cleanExpiredSessions() {
        $files = glob($this->sessionDir . '/*.json');
        if ($files) {
            foreach ($files as $file) {
                $mtime = filemtime($file);
                if (time() - $mtime > $this->sessionExpire) {
                    unlink($file);
                }
            }
        }
    }

    private function generateSessionId() {
        return bin2hex(random_bytes(32));
    }

    private function getSessionFilePath($sessionId) {
        return $this->sessionDir . '/' . $sessionId . '.json';
    }
}
