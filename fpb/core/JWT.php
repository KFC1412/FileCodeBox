<?php

class JWT {
    private static $secretKey = 'filephpbox_jwt_secret_key';
    private static $algorithm = 'HS256';

    public static function setSecretKey($key) {
        self::$secretKey = $key;
    }

    public static function encode($payload, $expireMinutes = 60) {
        if (!isset($payload['exp'])) {
            $payload['exp'] = time() + ($expireMinutes * 60);
        }
        $payload['iat'] = time();

        $header = json_encode(['alg' => self::$algorithm, 'typ' => 'JWT']);
        $payloadJson = json_encode($payload);

        $encodedHeader = self::base64UrlEncode($header);
        $encodedPayload = self::base64UrlEncode($payloadJson);

        $signature = self::sign($encodedHeader . '.' . $encodedPayload);

        return $encodedHeader . '.' . $encodedPayload . '.' . $signature;
    }

    public static function decode($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        list($encodedHeader, $encodedPayload, $signature) = $parts;

        $header = json_decode(self::base64UrlDecode($encodedHeader), true);
        $payload = json_decode(self::base64UrlDecode($encodedPayload), true);

        if (!$header || !$payload) {
            return null;
        }

        if (!self::verify($encodedHeader . '.' . $encodedPayload, $signature)) {
            return null;
        }

        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    public static function refresh($token) {
        $payload = self::decode($token);
        if (!$payload) {
            return null;
        }
        unset($payload['exp'], $payload['iat']);
        return self::encode($payload);
    }

    public static function getTokenFromHeader() {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (strpos($auth, 'Bearer ') === 0) {
            return substr($auth, 7);
        }
        return null;
    }

    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private static function sign($data) {
        return hash_hmac('sha256', $data, self::$secretKey, true);
    }

    private static function verify($data, $signature) {
        $expected = self::sign($data);
        return hash_equals($expected, $signature);
    }
}
