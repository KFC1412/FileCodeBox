<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../core/Response.php';
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

function handleAdminApi() {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($uri, PHP_URL_PATH);
    $path = str_replace('/api/admin', '', $path);
    $path = str_replace('/admin', '', $path);

    $storage = Storage::getStorage();

    switch ($path) {
        case '/login':
            if ($method === 'POST') {
                handleLogin();
            }
            break;

        case '/file/delete':
            if ($method === 'DELETE') {
                handleFileDelete($storage);
            }
            break;

        case '/file/list':
            if ($method === 'GET') {
                handleFileList();
            }
            break;

        case '/config/get':
            if ($method === 'GET') {
                handleConfigGet();
            }
            break;

        case '/config/update':
            if ($method === 'PATCH') {
                handleConfigUpdate();
            }
            break;

        case '/file/download':
            if ($method === 'GET') {
                handleFileDownload($storage);
            }
            break;

        default:
            Response::notFound('API不存在');
    }
}

function handleLogin() {
    checkAdmin(true);
    Response::success();
}

function handleFileDelete($storage) {
    checkAdmin(true);

    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? 0;

    if (!$id) {
        Response::forbidden('缺少文件ID');
    }

    $fileCode = FileCodes::findById($id);

    if (!$fileCode) {
        Response::notFound('文件不存在');
    }

    $storage->deleteFile($fileCode);
    $fileCode->delete();

    Response::success();
}

function handleFileList() {
    checkAdmin(true);

    $page = intval($_GET['page'] ?? 1);
    $size = intval($_GET['size'] ?? 10);
    $offset = ($page - 1) * $size;

    $files = FileCodes::all($size, $offset);
    $total = FileCodes::count();

    $data = array_map(function($file) {
        return $file->toArray();
    }, $files);

    Response::success([
        'page' => $page,
        'size' => $size,
        'data' => $data,
        'total' => $total
    ]);
}

function handleConfigGet() {
    checkAdmin(true);
    global $settings, $default_config;

    $config = [];
    foreach ($default_config as $key => $value) {
        $config[$key] = $settings[$key] ?? $value;
    }

    Response::success($config);
}

function handleConfigUpdate() {
    checkAdmin(true);

    $input = json_decode(file_get_contents('php://input'), true);
    global $settings, $default_config;

    $adminToken = $input['admin_token'] ?? null;
    if ($adminToken === null || $adminToken === '') {
        Response::forbidden('管理员密码不能为空');
    }

    $intFields = ['errorCount', 'errorMinute', 'max_save_seconds', 'onedrive_proxy', 'openUpload', 'port', 's3_proxy', 'uploadCount', 'uploadMinute', 'uploadSize'];
    $floatFields = ['opacity'];

    $newSettings = [];
    foreach ($input as $key => $value) {
        if (!array_key_exists($key, $default_config)) {
            continue;
        }

        if (in_array($key, $intFields)) {
            $newSettings[$key] = intval($value);
        } elseif (in_array($key, $floatFields)) {
            $newSettings[$key] = floatval($value);
        } else {
            $newSettings[$key] = $value;
        }
    }

    KeyValue::set('settings', $newSettings);

    foreach ($newSettings as $key => $value) {
        $settings[$key] = $value;
    }

    Response::success();
}

function handleFileDownload($storage) {
    checkAdmin(true);

    $id = intval($_GET['id'] ?? 0);

    if (!$id) {
        Response::forbidden('缺少文件ID');
    }

    $fileCode = FileCodes::findById($id);

    if (!$fileCode) {
        Response::notFound('文件不存在');
    }

    if ($fileCode->text) {
        Response::success($fileCode->text);
    } else {
        $storage->getFileResponse($fileCode);
    }
}
