<?php

define('BASE_DIR', dirname(__DIR__));
define('DATA_ROOT', BASE_DIR . '/data');

if (!file_exists(DATA_ROOT)) {
    mkdir(DATA_ROOT, 0755, true);
}

$default_config = [
    'file_storage' => 'local',
    'name' => '文件快递柜 - FileCodeBox',
    'description' => '开箱即用的文件快传系统',
    'notify_title' => '系统通知',
    'notify_content' => '欢迎使用 FileCodeBox，本程序开源于 <a href="https://github.com/vastsa/FileCodeBox" target="_blank">Github</a> ，欢迎Star和Fork。',
    'page_explain' => '请勿上传或分享违法内容。根据《中华人民共和国网络安全法》、《中华人民共和国刑法》、《中华人民共和国治安管理处罚法》等相关规定。 传播或存储违法、违规内容，会受到相关处罚，严重者将承担刑事责任。本站坚决配合相关部门，确保网络内容的安全，和谐，打造绿色网络环境。',
    'keywords' => 'FileCodeBox, 文件快递柜, 口令传送箱, 匿名口令分享文本, 文件',
    's3_access_key_id' => '',
    's3_secret_access_key' => '',
    's3_bucket_name' => '',
    's3_endpoint_url' => '',
    's3_region_name' => 'auto',
    's3_signature_version' => 's3v2',
    's3_hostname' => '',
    's3_proxy' => 0,
    'max_save_seconds' => 0,
    'aws_session_token' => '',
    'onedrive_domain' => '',
    'onedrive_client_id' => '',
    'onedrive_username' => '',
    'onedrive_password' => '',
    'onedrive_root_path' => 'filebox_storage',
    'onedrive_proxy' => 0,
    'admin_token' => 'zxc123456',
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
