<?php

class LoginGuard {
    private $maxAttempts = 5;
    private $lockoutMinutes = 15;
    private $storageDir;

    public function __construct() {
        $this->storageDir = DATA_ROOT . '/login_attempts';
        if (!file_exists($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    public function recordAttempt($username) {
        $filePath = $this->getFilePath($username);
        $now = time();

        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true);
            $data['attempts'] = ($data['attempts'] ?? 0) + 1;
            $data['last_attempt'] = $now;
            file_put_contents($filePath, json_encode($data));
        } else {
            file_put_contents($filePath, json_encode(['attempts' => 1, 'last_attempt' => $now]));
        }

        return $this->isLockedOut($username);
    }

    public function isLockedOut($username) {
        $filePath = $this->getFilePath($username);
        if (!file_exists($filePath)) {
            return false;
        }

        $data = json_decode(file_get_contents($filePath), true);
        if (!$data) {
            return false;
        }

        if ($data['attempts'] >= $this->maxAttempts) {
            $now = time();
            if ($now - $data['last_attempt'] < ($this->lockoutMinutes * 60)) {
                return true;
            }
            $this->resetAttempts($username);
        }

        return false;
    }

    public function resetAttempts($username) {
        $filePath = $this->getFilePath($username);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    public function getRemainingAttempts($username) {
        $filePath = $this->getFilePath($username);
        if (!file_exists($filePath)) {
            return $this->maxAttempts;
        }

        $data = json_decode(file_get_contents($filePath), true);
        $attempts = $data['attempts'] ?? 0;
        return max(0, $this->maxAttempts - $attempts);
    }

    public function getLockoutRemaining($username) {
        $filePath = $this->getFilePath($username);
        if (!file_exists($filePath)) {
            return 0;
        }

        $data = json_decode(file_get_contents($filePath), true);
        if ($data['attempts'] >= $this->maxAttempts) {
            $now = time();
            $remaining = ($this->lockoutMinutes * 60) - ($now - $data['last_attempt']);
            return max(0, $remaining);
        }

        return 0;
    }

    private function getFilePath($username) {
        return $this->storageDir . '/' . md5($username) . '.json';
    }
}
