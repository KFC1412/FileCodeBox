<?php

class RateLimit {
    private static $limits = [];

    public static function check($type, $ip) {
        global $settings;

        if (!isset(self::$limits[$type])) {
            $count = $type === 'error' ? $settings['errorCount'] : $settings['uploadCount'];
            $minutes = $type === 'error' ? $settings['errorMinute'] : $settings['uploadMinute'];
            self::$limits[$type] = [
                'count' => $count,
                'minutes' => $minutes,
                'ips' => []
            ];
        }

        $now = time();
        $key = $type . '_' . md5($ip);

        if (!isset(self::$limits[$type]['ips'][$key])) {
            self::$limits[$type]['ips'][$key] = ['count' => 0, 'reset_time' => $now + (self::$limits[$type]['minutes'] * 60)];
        }

        $record = &self::$limits[$type]['ips'][$key];

        if ($now > $record['reset_time']) {
            $record = ['count' => 0, 'reset_time' => $now + (self::$limits[$type]['minutes'] * 60)];
        }

        if ($record['count'] >= self::$limits[$type]['count']) {
            return false;
        }

        return true;
    }

    public static function addHit($type, $ip) {
        $key = $type . '_' . md5($ip);
        if (isset(self::$limits[$type]['ips'][$key])) {
            self::$limits[$type]['ips'][$key]['count']++;
        }
    }

    public static function getClientIp() {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
