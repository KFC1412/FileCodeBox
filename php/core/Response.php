<?php

class Response {
    public static function json($data = null, $code = 200, $message = 'ok') {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode([
            'code' => $code,
            'message' => $message,
            'detail' => $data
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function success($data = null) {
        self::json($data, 200, 'ok');
    }

    public static function error($message, $code = 400) {
        self::json($message, $code, 'error');
    }

    public static function notFound($message = 'Not Found') {
        self::json($message, 404, 'not found');
    }

    public static function unauthorized($message = 'Unauthorized') {
        self::json($message, 401, 'unauthorized');
    }

    public static function forbidden($message = 'Forbidden') {
        self::json($message, 403, 'forbidden');
    }
}
