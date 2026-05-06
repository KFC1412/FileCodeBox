<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/RateLimit.php';
require_once __DIR__ . '/../core/Utils.php';
require_once __DIR__ . '/../core/Storage.php';
require_once __DIR__ . '/../models/FileCodes.php';
require_once __DIR__ . '/../models/KeyValue.php';

function checkAdmin($requireAdmin = true) {
    global $settings;

    $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if ($requireAdmin && $auth !== $settings['admin_token']) {
        Response::unauthorized('未授权或授权校验失败');
    }

    if (!$requireAdmin && !$settings['openUpload'] && $auth !== $settings['admin_token']) {
        Response::forbidden('本站未开启游客上传，如需上传请先登录后台');
    }

    return $auth === $settings['admin_token'];
}

function handleShareApi() {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH);
    $path = str_replace('/api/share', '', $path);
    $path = str_replace('/share', '', $path);

    $ip = RateLimit::getClientIp();
    $storage = Storage::getStorage();

    switch ($path) {
        case '/text/':
            if ($method === 'POST') {
                handleShareText($ip);
            }
            break;

        case '/file/':
            if ($method === 'POST') {
                handleShareFile($ip, $storage);
            }
            break;

        case '/select/':
            if ($method === 'GET') {
                handleSelectFile($ip, $storage);
            } elseif ($method === 'POST') {
                handleSelectFilePost($ip, $storage);
            }
            break;

        case '/download':
            if ($method === 'GET') {
                handleDownload($ip, $storage);
            }
            break;

        default:
            Response::notFound('API不存在');
    }
}

function handleShareText($ip) {
    checkAdmin(false);

    if (!RateLimit::check('upload', $ip)) {
        Response::forbidden('上传过于频繁，请稍后再试');
    }

    $text = $_POST['text'] ?? '';
    $expireValue = intval($_POST['expire_value'] ?? 1);
    $expireStyle = $_POST['expire_style'] ?? 'day';

    if (empty($text)) {
        Response::forbidden('文本内容不能为空');
    }

    $textSize = strlen($text);
    $maxTxtSize = 222 * 1024;
    if ($textSize > $maxTxtSize) {
        Response::forbidden('内容过多，建议采用文件形式');
    }

    $expireInfo = Utils::getExpireInfo($expireValue, $expireStyle);

    $fileCode = FileCodes::create([
        'code' => $expireInfo['code'],
        'text' => $text,
        'expired_at' => $expireInfo['expired_at'],
        'expired_count' => $expireInfo['expired_count'],
        'used_count' => $expireInfo['used_count'],
        'size' => $textSize,
        'prefix' => '文本分享'
    ]);

    RateLimit::addHit('upload', $ip);
    Response::success(['code' => $fileCode->code]);
}

function handleShareFile($ip, $storage) {
    checkAdmin(false);

    if (!RateLimit::check('upload', $ip)) {
        Response::forbidden('上传过于频繁，请稍后再试');
    }

    global $settings;

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        Response::forbidden('文件上传失败');
    }

    $file = $_FILES['file'];
    $fileSize = $file['size'];

    if ($fileSize > $settings['uploadSize']) {
        $maxSizeMb = $settings['uploadSize'] / (1024 * 1024);
        Response::forbidden("大小超过限制，最大为{$maxSizeMb} MB");
    }

    $expireValue = intval($_POST['expire_value'] ?? 1);
    $expireStyle = $_POST['expire_style'] ?? 'day';

    if (!in_array($expireStyle, $settings['expireStyle'])) {
        Response::forbidden('过期时间类型错误');
    }

    $expireInfo = Utils::getExpireInfo($expireValue, $expireStyle);
    $fileInfo = Utils::getFilePathName($file['name']);

    $storage->saveFile($file, $fileInfo['save_path']);

    $fileCode = FileCodes::create([
        'code' => $expireInfo['code'],
        'prefix' => $fileInfo['prefix'],
        'suffix' => $fileInfo['suffix'],
        'uuid_file_name' => $fileInfo['uuid_file_name'],
        'file_path' => $fileInfo['path'],
        'size' => $fileSize,
        'expired_at' => $expireInfo['expired_at'],
        'expired_count' => $expireInfo['expired_count'],
        'used_count' => $expireInfo['used_count']
    ]);

    RateLimit::addHit('upload', $ip);
    Response::success([
        'code' => $fileCode->code,
        'name' => $file['name']
    ]);
}

function handleSelectFile($ip, $storage) {
    $code = $_GET['code'] ?? '';

    if (empty($code)) {
        Response::forbidden('缺少code参数');
    }

    $fileCode = FileCodes::findByCode($code);

    if (!$fileCode) {
        RateLimit::addHit('error', $ip);
        Response::notFound('文件不存在');
    }

    if ($fileCode->isExpired()) {
        RateLimit::addHit('error', $ip);
        Response::notFound('文件已过期');
    }

    $fileCode->used_count++;
    if ($fileCode->expired_count > 0) {
        $fileCode->expired_count--;
    }
    $fileCode->save();

    $storage->getFileResponse($fileCode);
}

function handleSelectFilePost($ip, $storage) {
    $input = json_decode(file_get_contents('php://input'), true);
    $code = $input['code'] ?? '';

    if (empty($code)) {
        Response::forbidden('缺少code参数');
    }

    $fileCode = FileCodes::findByCode($code);

    if (!$fileCode) {
        RateLimit::addHit('error', $ip);
        Response::notFound('文件不存在');
    }

    if ($fileCode->isExpired()) {
        RateLimit::addHit('error', $ip);
        Response::notFound('文件已过期');
    }

    $fileCode->used_count++;
    if ($fileCode->expired_count > 0) {
        $fileCode->expired_count--;
    }
    $fileCode->save();

    if ($fileCode->text) {
        Response::success([
            'code' => $fileCode->code,
            'name' => $fileCode->prefix . $fileCode->suffix,
            'size' => $fileCode->size,
            'text' => $fileCode->text
        ]);
    } else {
        Response::success([
            'code' => $fileCode->code,
            'name' => $fileCode->prefix . $fileCode->suffix,
            'size' => $fileCode->size,
            'text' => $storage->getFileUrl($fileCode)
        ]);
    }
}

function handleDownload($ip, $storage) {
    $key = $_GET['key'] ?? '';
    $code = $_GET['code'] ?? '';

    if (empty($code)) {
        Response::forbidden('缺少code参数');
    }

    $expectedKey = Utils::getDownloadToken($code);
    if ($key !== $expectedKey) {
        RateLimit::addHit('error', $ip);
        Response::unauthorized('无效的下载令牌');
    }

    $fileCode = FileCodes::findByCode($code);

    if (!$fileCode) {
        Response::notFound('文件不存在');
    }

    if ($fileCode->text) {
        Response::success($fileCode->text);
    } else {
        $storage->getFileResponse($fileCode);
    }
}
