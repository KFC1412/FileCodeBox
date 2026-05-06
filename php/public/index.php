<?php

require_once __DIR__ . '/../config.php';

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

if (strpos($path, '/api/share') === 0 || strpos($path, '/share') === 0) {
    require_once __DIR__ . '/api/share.php';
    handleShareApi();
} elseif (strpos($path, '/api/admin') === 0 || strpos($path, '/admin') === 0) {
    require_once __DIR__ . '/admin.php';
    handleAdminApi();
} elseif ($path === '/' || $path === '/index.php') {
    $indexFile = __DIR__ . '/../../fcb-fronted/dist/index.html';
    if (file_exists($indexFile)) {
        $content = file_get_contents($indexFile);
        $content = str_replace('{{title}}', htmlspecialchars($settings['name']), $content);
        $content = str_replace('{{description}}', htmlspecialchars($settings['description']), $content);
        $content = str_replace('{{keywords}}', htmlspecialchars($settings['keywords']), $content);
        $content = str_replace('{{opacity}}', strval($settings['opacity']), $content);
        $content = str_replace('{{background}}', htmlspecialchars($settings['background']), $content);

        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache');
        echo $content;
    } else {
        http_response_code(404);
        echo 'index.html not found';
    }
} elseif ($path === '/robots.txt') {
    header('Content-Type: text/plain');
    echo $settings['robotsText'];
} elseif (strpos($path, '/assets/') === 0) {
    $filePath = __DIR__ . '/../../fcb-fronted/dist' . $path;
    if (file_exists($filePath) && is_file($filePath)) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $mimeTypes = [
            'js' => 'application/javascript',
            'css' => 'text/css',
            'html' => 'text/html',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject',
        ];
        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        readfile($filePath);
    } else {
        http_response_code(404);
        echo 'File not found: ' . $path;
    }
} else {
    $filePath = __DIR__ . '/../../fcb-fronted/dist/index.html';
    if (file_exists($filePath)) {
        header('Content-Type: text/html; charset=utf-8');
        echo file_get_contents($filePath);
    } else {
        http_response_code(404);
        echo 'Not found';
    }
}
