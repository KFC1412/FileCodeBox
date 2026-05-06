<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Utils.php';
require_once __DIR__ . '/../core/Storage.php';
require_once __DIR__ . '/../core/Statistics.php';
require_once __DIR__ . '/../core/Logger.php';
require_once __DIR__ . '/../core/LoginGuard.php';
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
    $stats = new Statistics();
    $logger = new Logger();

    switch ($path) {
        case '/login':
            if ($method === 'POST') {
                handleLogin($logger);
            }
            break;

        case '/file/delete':
            if ($method === 'DELETE') {
                handleFileDelete($storage, $logger);
            }
            break;

        case '/file/delete/batch':
            if ($method === 'DELETE') {
                handleBatchDelete($storage, $logger);
            }
            break;

        case '/file/list':
            if ($method === 'GET') {
                handleFileList();
            }
            break;

        case '/file/search':
            if ($method === 'GET') {
                handleFileSearch();
            }
            break;

        case '/file/clean':
            if ($method === 'POST') {
                handleCleanExpired($storage, $logger);
            }
            break;

        case '/file/export':
            if ($method === 'GET') {
                handleFileExport();
            }
            break;

        case '/file/download':
            if ($method === 'GET') {
                handleFileDownload($storage);
            }
            break;

        case '/config/get':
            if ($method === 'GET') {
                handleConfigGet();
            }
            break;

        case '/config/update':
            if ($method === 'PATCH') {
                handleConfigUpdate($logger);
            }
            break;

        case '/password/change':
            if ($method === 'PATCH') {
                handlePasswordChange($logger);
            }
            break;

        case '/stats':
            if ($method === 'GET') {
                handleStats($stats);
            }
            break;

        case '/logs':
            if ($method === 'GET') {
                handleLogs($logger);
            }
            break;

        case '/logs/clear':
            if ($method === 'POST') {
                handleClearLogs($logger);
            }
            break;

        case '/lang/get':
            if ($method === 'GET') {
                handleLangGet();
            }
            break;

        case '/lang/set':
            if ($method === 'POST') {
                handleLangSet();
            }
            break;

        default:
            Response::notFound('API不存在');
    }
}

function handleLogin($logger) {
    global $settings;
    
    $input = json_decode(file_get_contents('php://input'), true);
    $password = $input['password'] ?? '';

    $loginGuard = new LoginGuard();
    $lockedOut = $loginGuard->isLockedOut('admin');
    
    if ($lockedOut) {
        $remaining = $loginGuard->getLockoutRemaining('admin');
        Response::forbidden("账户已锁定，请 {$remaining} 秒后重试");
    }

    if ($password !== $settings['admin_token']) {
        $loginGuard->recordAttempt('admin');
        $remaining = $loginGuard->getRemainingAttempts('admin');
        Response::unauthorized("密码错误，剩余 {$remaining} 次尝试机会");
    }

    $loginGuard->resetAttempts('admin');
    $logger->recordAction('login', 'admin', 'success');
    Response::success();
}

function handleFileDelete($storage, $logger) {
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

    $fileName = $fileCode->prefix . $fileCode->suffix;
    $storage->deleteFile($fileCode);
    $fileCode->delete();

    $logger->recordAction('delete_file', $fileName, 'success');
    Response::success();
}

function handleBatchDelete($storage, $logger) {
    checkAdmin(true);

    $input = json_decode(file_get_contents('php://input'), true);
    $ids = $input['ids'] ?? [];

    if (!is_array($ids) || empty($ids)) {
        Response::forbidden('缺少文件ID列表');
    }

    $deletedCount = 0;
    foreach ($ids as $id) {
        $fileCode = FileCodes::findById($id);
        if ($fileCode) {
            $storage->deleteFile($fileCode);
            $fileCode->delete();
            $deletedCount++;
        }
    }

    $logger->recordAction('batch_delete_files', "删除 {$deletedCount} 个文件", 'success');
    Response::success(['deleted_count' => $deletedCount]);
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

function handleFileSearch() {
    checkAdmin(true);

    $keyword = $_GET['keyword'] ?? '';
    $page = intval($_GET['page'] ?? 1);
    $size = intval($_GET['size'] ?? 10);

    if (!$keyword) {
        handleFileList();
        return;
    }

    $allFiles = FileCodes::all();
    $filtered = array_filter($allFiles, function($file) use ($keyword) {
        return strpos(strtolower($file->code), strtolower($keyword)) !== false ||
               strpos(strtolower($file->prefix), strtolower($keyword)) !== false ||
               strpos(strtolower($file->suffix), strtolower($keyword)) !== false;
    });

    $total = count($filtered);
    $files = array_slice($filtered, ($page - 1) * $size, $size);

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

function handleCleanExpired($storage, $logger) {
    checkAdmin(true);

    $files = FileCodes::all();
    $deletedCount = 0;

    foreach ($files as $file) {
        if ($file->isExpired()) {
            $storage->deleteFile($file);
            $file->delete();
            $deletedCount++;
        }
    }

    $logger->recordAction('clean_expired_files', "清理 {$deletedCount} 个过期文件", 'success');
    Response::success(['cleaned_count' => $deletedCount]);
}

function handleFileExport() {
    checkAdmin(true);

    $files = FileCodes::all();
    $data = array_map(function($file) {
        return $file->toArray();
    }, $files);

    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="filecodes_export_' . date('YmdHis') . '.json"');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
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

function handleConfigGet() {
    checkAdmin(true);
    global $settings, $default_config;

    $config = [];
    foreach ($default_config as $key => $value) {
        $config[$key] = $settings[$key] ?? $value;
    }

    Response::success($config);
}

function handleConfigUpdate($logger) {
    checkAdmin(true);

    $input = json_decode(file_get_contents('php://input'), true);
    global $settings, $default_config;

    $intFields = ['errorCount', 'errorMinute', 'max_save_seconds', 'onedrive_proxy', 'openUpload', 'port', 's3_proxy', 'uploadCount', 'uploadMinute', 'uploadSize', 'max_upload_size', 'session_expire_minutes', 'max_login_attempts', 'login_lockout_minutes', 'storage_warning_threshold'];
    $floatFields = ['opacity'];
    $boolFields = ['enable_file_encryption', 'enable_qrcode', 'enable_password_protection', 'enable_preview'];

    $newSettings = [];
    foreach ($input as $key => $value) {
        if (!array_key_exists($key, $default_config)) {
            continue;
        }

        if (in_array($key, $intFields)) {
            $newSettings[$key] = intval($value);
        } elseif (in_array($key, $floatFields)) {
            $newSettings[$key] = floatval($value);
        } elseif (in_array($key, $boolFields)) {
            $newSettings[$key] = boolval($value);
        } else {
            $newSettings[$key] = $value;
        }
    }

    save_settings($newSettings);

    $logger->recordAction('update_config', json_encode(array_keys($newSettings)), 'success');
    Response::success();
}

function handlePasswordChange($logger) {
    checkAdmin(true);

    $input = json_decode(file_get_contents('php://input'), true);
    $oldPassword = $input['old_password'] ?? '';
    $newPassword = $input['new_password'] ?? '';

    global $settings;

    if ($oldPassword !== $settings['admin_token']) {
        Response::forbidden('旧密码错误');
    }

    if (strlen($newPassword) < 6) {
        Response::forbidden('新密码长度不能少于6位');
    }

    save_settings(['admin_token' => $newPassword]);

    $logger->recordAction('change_password', 'admin', 'success');
    Response::success();
}

function handleStats($stats) {
    checkAdmin(true);

    $fileStats = $stats->getFileStatistics();
    $storageWarning = $stats->getStorageWarning();
    $recentStats = $stats->getRecentStats(7);
    $totalStats = $stats->getTotalStats();

    Response::success([
        'files' => $fileStats,
        'storage' => $storageWarning,
        'recent' => $recentStats,
        'total' => $totalStats
    ]);
}

function handleLogs($logger) {
    checkAdmin(true);

    $type = $_GET['type'] ?? 'all';
    $limit = intval($_GET['limit'] ?? 100);

    $result = [];
    if ($type === 'all' || $type === 'actions') {
        $result['actions'] = $logger->getActionLogs($limit);
    }
    if ($type === 'all' || $type === 'access') {
        $result['access'] = $logger->getAccessLogs($limit);
    }
    if ($type === 'all' || $type === 'app') {
        $result['app'] = $logger->getAppLogs($limit);
    }

    Response::success($result);
}

function handleClearLogs($logger) {
    checkAdmin(true);

    $input = json_decode(file_get_contents('php://input'), true);
    $type = $input['type'] ?? 'all';

    $logger->clearLogs($type);
    $logger->recordAction('clear_logs', $type, 'success');

    Response::success();
}

function handleLangGet() {
    checkAdmin(true);

    $langs = Translator::getAvailableLangs();
    $currentLang = Translator::getLang();

    Response::success([
        'current' => $currentLang,
        'available' => $langs
    ]);
}

function handleLangSet() {
    checkAdmin(true);

    $input = json_decode(file_get_contents('php://input'), true);
    $lang = $input['lang'] ?? 'zh';

    save_settings(['default_lang' => $lang]);
    Translator::setLang($lang);

    Response::success();
}
