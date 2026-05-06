<?php

class ShareEnhancer {
    public static function generatePassword($length = 8) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }

    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function generateQRCode($url, $size = 200) {
        $encodedUrl = urlencode($url);
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedUrl}";
        return $qrUrl;
    }

    public static function generateShortCode($length = 6) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }

    public static function getShareUrl($code, $baseUrl = null) {
        if (!$baseUrl) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            $baseUrl = $protocol . '://' . $host;
        }
        return $baseUrl . '/share/' . $code;
    }

    public static function formatExpireTime($expiredAt) {
        if (!$expiredAt) {
            return '永久';
        }

        $expire = new DateTime($expiredAt);
        $now = new DateTime();
        $diff = $now->diff($expire);

        if ($diff->y > 0) {
            return $diff->y . '年后';
        }
        if ($diff->m > 0) {
            return $diff->m . '个月后';
        }
        if ($diff->d > 0) {
            return $diff->d . '天后';
        }
        if ($diff->h > 0) {
            return $diff->h . '小时后';
        }
        if ($diff->i > 0) {
            return $diff->i . '分钟后';
        }
        return '即将过期';
    }

    public static function generateDownloadToken($code, $expireSeconds = 3600) {
        $timestamp = time() + $expireSeconds;
        $token = hash('sha256', $code . $timestamp . 'fpb_download_token');
        return [
            'token' => $token,
            'expire' => $timestamp
        ];
    }

    public static function verifyDownloadToken($code, $token) {
        $now = time();
        $validSeconds = 3600;
        
        for ($i = 0; $i <= $validSeconds; $i += 60) {
            $timestamp = $now - $i;
            $expectedToken = hash('sha256', $code . $timestamp . 'fpb_download_token');
            if (hash_equals($expectedToken, $token)) {
                return true;
            }
        }
        
        return false;
    }
}
