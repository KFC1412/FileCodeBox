<?php

class Utils {
    public static function generateCode($style = 'num') {
        $pdo = Database::getConnection();

        do {
            if ($style === 'string') {
                $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $code = '';
                for ($i = 0; $i < 5; $i++) {
                    $code .= $chars[random_int(0, strlen($chars) - 1)];
                }
            } else {
                $code = random_int(10000, 99999);
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM file_codes WHERE code = ?");
            $stmt->execute([$code]);
            $exists = $stmt->fetchColumn() > 0;
        } while ($exists);

        return $code;
    }

    public static function generateUuid() {
        return bin2hex(random_bytes(16));
    }

    public static function getFilePathName($filename) {
        $today = date('Y/m/d');
        $path = "share/data/{$today}";
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $prefix = pathinfo($filename, PATHINFO_FILENAME);
        $uuid = self::generateUuid();
        $uuidFileName = $uuid . ($ext ? ".$ext" : '');
        $savePath = "{$path}/{$uuidFileName}";

        return [
            'path' => $path,
            'suffix' => $ext ? ".$ext" : '',
            'prefix' => $prefix,
            'uuid_file_name' => $uuidFileName,
            'save_path' => $savePath
        ];
    }

    public static function getExpireInfo($expireValue, $expireStyle) {
        global $settings;

        $now = new DateTime();
        $expiredCount = -1;
        $usedCount = 0;
        $code = null;

        $maxSaveSeconds = intval($settings['max_save_seconds']);
        if ($maxSaveSeconds > 0) {
            $maxTimedelta = new DateInterval("PT{$maxSaveSeconds}S");
        } else {
            $maxTimedelta = new DateInterval('P7D');
        }

        switch ($expireStyle) {
            case 'day':
                $interval = new DateInterval("P{$expireValue}D");
                if ($interval > $maxTimedelta) {
                    Response::forbidden('超过最大保存时间限制');
                }
                $expiredAt = (clone $now)->add($interval);
                break;
            case 'hour':
                $interval = new DateInterval("PT{$expireValue}H");
                if ($interval > $maxTimedelta) {
                    Response::forbidden('超过最大保存时间限制');
                }
                $expiredAt = (clone $now)->add($interval);
                break;
            case 'minute':
                $interval = new DateInterval("PT{$expireValue}M");
                if ($interval > $maxTimedelta) {
                    Response::forbidden('超过最大保存时间限制');
                }
                $expiredAt = (clone $now)->add($interval);
                break;
            case 'count':
                $expiredAt = (clone $now)->add(new DateInterval('P1D'));
                $expiredCount = $expireValue;
                break;
            case 'forever':
                $expiredAt = null;
                $code = self::generateCode('string');
                break;
            default:
                $expiredAt = (clone $now)->add(new DateInterval('P1D'));
        }

        if ($code === null) {
            $code = self::generateCode();
        }

        return [
            'expired_at' => $expiredAt?->format('Y-m-d H:i:s'),
            'expired_count' => $expiredCount,
            'used_count' => $usedCount,
            'code' => $code
        ];
    }

    public static function isExpired($fileCode) {
        if ($fileCode['expired_at'] === null) {
            return false;
        }

        if ($fileCode['expired_count'] < 0) {
            $expiredAt = new DateTime($fileCode['expired_at']);
            return $expiredAt < new DateTime();
        }

        return $fileCode['expired_count'] <= 0;
    }

    public static function getDownloadToken($code) {
        $token = '123456';
        $timestamp = intval(time() / 1000) * 1000;
        return hash('sha256', "{$code}{$timestamp}000{$token}");
    }

    public static function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public static function getFileUrl($code) {
        return "/api/share/download?key=" . self::getDownloadToken($code) . "&code={$code}";
    }
}
