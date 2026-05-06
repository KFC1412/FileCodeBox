<?php

require_once __DIR__ . '/config.php';

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

if (strpos($path, '/api/share') === 0 || strpos($path, '/share') === 0) {
    require_once __DIR__ . '/api/share.php';
    handleShareApi();
} elseif (strpos($path, '/api/admin') === 0 || strpos($path, '/admin') === 0) {
    require_once __DIR__ . '/admin.php';
    handleAdminApi();
} elseif ($path === '/' || $path === '/index.php') {
    serveIndexHtml();
} elseif ($path === '/robots.txt') {
    header('Content-Type: text/plain');
    echo $settings['robotsText'];
} elseif (strpos($path, '/assets/') === 0) {
    serveAssets($path);
} else {
    serveIndexHtml();
}

function serveIndexHtml() {
    global $settings;

    $indexFile = __DIR__ . '/index.html';

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
        echo '<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FileCodeBox - 文件快递柜</title>
    <style>
        body { font-family: system-ui, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background: #f5f5f5; }
        .container { text-align: center; padding: 2rem; background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #409eff; }
        .message { color: #666; margin-top: 1rem; }
        .btn { display: inline-block; margin-top: 1.5rem; padding: 10px 30px; background: #409eff; color: white; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>FileCodeBox</h1>
        <p class="message">请先构建前端或放置 index.html 文件</p>
        <p class="message">前端目录: ../fcb-fronted/dist/</p>
        <a href="https://github.com/vastsa/FileCodeBox" class="btn" target="_blank">GitHub</a>
    </div>
</body>
</html>';
    }
}

function serveAssets($path) {
    $filePath = __DIR__ . $path;

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
            'ico' => 'image/x-icon',
            'webp' => 'image/webp',
            'woff2' => 'font/woff2',
        ];
        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        header('Cache-Control: public, max-age=31536000, immutable');
        readfile($filePath);
    } else {
        http_response_code(404);
        echo 'Asset not found: ' . htmlspecialchars($path);
    }
}
