<?php

define('BASE_DIR', dirname(__DIR__));
define('DATA_ROOT', BASE_DIR . '/data');

if (!file_exists(DATA_ROOT)) {
    mkdir(DATA_ROOT, 0755, true);
}

$default_config = [
    'file_storage' => 'local',
    'name' => 'FilePhpBox',
    'name_zh' => '文件快递柜',
    'description' => 'Lightweight file sharing system based on PHP + JSON',
    'description_zh' => '基于 PHP + JSON 的轻量级文件分享系统',
    'notify_title' => 'System Notification',
    'notify_title_zh' => '系统通知',
    'notify_content' => 'Welcome to FilePhpBox (FPB)',
    'notify_content_zh' => '欢迎使用 FilePhpBox (FPB)',
    'page_explain' => 'Do not upload or share illegal content.',
    'page_explain_zh' => '请勿上传或分享违法内容。',
    'keywords' => 'FilePhpBox, FPB, file sharing, PHP',
    'keywords_zh' => 'FilePhpBox, FPB, 文件快递柜, 文件分享',
    'default_lang' => 'zh',
    'allowed_extensions' => 'jpg,jpeg,png,gif,bmp,webp,svg,txt,md,pdf,doc,docx,xls,xlsx,ppt,pptx,zip,rar,7z,tar,gz,php,js,html,css,json,xml,py,java,cpp,mp3,wav,ogg,flac,mp4,webm',
    'blocked_extensions' => 'exe,com,bat,cmd,msi,dll,scr,pif,sys',
    'enable_file_encryption' => false,
    'encryption_key' => '',
    's3_access_key_id' => '',
    's3_secret_access_key' => '',
    's3_bucket_name' => '',
    's3_endpoint_url' => '',
    's3_region_name' => 'auto',
    's3_signature_version' => 's3v2',
    's3_hostname' => '',
    's3_proxy' => 0,
    'max_save_seconds' => 0,
    'max_upload_size' => 1024 * 1024 * 10,
    'aws_session_token' => '',
    'onedrive_domain' => '',
    'onedrive_client_id' => '',
    'onedrive_username' => '',
    'onedrive_password' => '',
    'onedrive_root_path' => 'fpb_storage',
    'onedrive_proxy' => 0,
    'admin_token' => 'zxc123456',
    'session_expire_minutes' => 60,
    'max_login_attempts' => 5,
    'login_lockout_minutes' => 15,
    'openUpload' => 1,
    'uploadSize' => 1024 * 1024 * 10,
    'expireStyle' => ['day', 'hour', 'minute', 'forever', 'count'],
    'uploadMinute' => 1,
    'uploadCount' => 10,
    'errorMinute' => 1,
    'errorCount' => 1,
    'port' => 12345,
    'showAdminAddr' => 0,
    'robotsText' => "User-agent: *\nDisallow: /",
    'opacity' => 0.9,
    'background' => '',
    'enable_qrcode' => true,
    'enable_password_protection' => true,
    'enable_preview' => true,
    'enable_thumbnails' => true,
    'storage_warning_threshold' => 90,
    'log_level' => 'info',
    'default_theme' => 'light',
    'enable_hot_files' => true,
    'hot_files_limit' => 10,
    'hot_files_days' => 7,
    'enable_share_history' => true,
    'max_history_days' => 30
];

$config_file = DATA_ROOT . '/config.json';
if (file_exists($config_file)) {
    $user_config = json_decode(file_get_contents($config_file), true) ?: [];
} else {
    $user_config = [];
}

$settings = array_merge($default_config, $user_config);

function get_setting($key, $default = null) {
    global $settings;
    return $settings[$key] ?? $default;
}

function save_settings($new_settings) {
    global $settings, $config_file;
    $settings = array_merge($settings, $new_settings);
    file_put_contents($config_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

require_once __DIR__ . '/core/Translator.php';
Translator::createDefaultLangFiles();
Translator::setLang($settings['default_lang']);
