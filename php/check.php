<?php

/**
 * FileCodeBox PHP 版本 - 一键部署检测脚本
 * 
 * 上传即部署说明:
 * 1. 将整个 php/ 目录上传到服务器的 Apache DocumentRoot
 * 2. 确保 Apache 启用了 mod_rewrite 和 mod_php
 * 3. 设置 data/ 目录可写权限
 * 4. 无需额外配置，直接访问即可运行
 */

$checks = [
    'PHP版本' => function() {
        return version_compare(PHP_VERSION, '7.4.0', '>=') ? 'OK' : 'FAIL - 需要 PHP 7.4+';
    },
    'PDO扩展' => function() {
        return extension_loaded('pdo') ? 'OK' : 'FAIL - 未安装 PDO';
    },
    'SQLite扩展' => function() {
        return extension_loaded('pdo_sqlite') ? 'OK' : 'FAIL - 未安装 SQLite';
    },
    'GD扩展' => function() {
        return extension_loaded('gd') ? 'OK' : 'WARN - GD扩展未安装(可选)';
    },
    'data目录可写' => function() {
        $dir = __DIR__ . '/data';
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        return is_writable($dir) ? 'OK' : 'FAIL - data目录不可写';
    },
    'index.html存在' => function() {
        return file_exists(__DIR__ . '/index.html') ? 'OK' : 'WARN - index.html不存在';
    },
];

$passed = true;
echo "<!DOCTYPE html><html lang='zh-CN'><head><meta charset='UTF-8'>";
echo "<title>FileCodeBox 部署检测</title>";
echo "<style>body{font-family:system-ui,sans-serif;margin:2rem;padding:2rem;background:#f5f5f5;}";
echo ".container{max-width:800px;margin:0 auto;background:white;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,0.1);padding:2rem;}";
echo "h1{color:#409eff;text-align:center;margin-bottom:2rem;}";
echo ".check{display:flex;justify-content:space-between;padding:0.5rem 1rem;border-bottom:1px solid #eee;}";
echo ".check:last-child{border-bottom:none;}";
echo ".ok{color:#67c23a;font-weight:bold;}";
echo ".fail{color:#f56c6c;font-weight:bold;}";
echo ".warn{color:#e6a23c;font-weight:bold;}";
echo ".success{text-align:center;margin-top:2rem;padding:1rem;background:#f0f9ff;border-radius:4px;}";
echo ".success a{color:#409eff;text-decoration:none;font-weight:bold;}";
echo ".failmsg{text-align:center;margin-top:2rem;padding:1rem;background:#fef0f0;border-radius:4px;color:#f56c6c;}";
echo "</style></head><body><div class='container'>";
echo "<h1>FileCodeBox 部署检测</h1>";

foreach ($checks as $name => $func) {
    $result = $func();
    $class = strpos($result, 'OK') !== false ? 'ok' : (strpos($result, 'WARN') !== false ? 'warn' : 'fail');
    if ($class === 'fail') $passed = false;
    echo "<div class='check'><span>$name</span><span class='$class'>$result</span></div>";
}

echo "<div class='check'><span>PHP版本</span><span class='ok'>" . PHP_VERSION . "</span></div>";

if ($passed) {
    echo "<div class='success'>";
    echo "<p>✅ 所有检测通过！</p>";
    echo "<p><a href='/'>点击访问首页</a></p>";
    echo "<p style='font-size:0.8rem;color:#999;margin-top:1rem'>管理后台: /#/admin | 默认密码: zxc123456</p>";
    echo "</div>";
} else {
    echo "<div class='failmsg'>";
    echo "<p>❌ 检测未通过，请修复以上问题后重试</p>";
    echo "</div>";
}

echo "</div></body></html>";
