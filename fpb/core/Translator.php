<?php

class Translator {
    private static $translations = [];
    private static $currentLang = 'zh';

    public static function setLang($lang) {
        self::$currentLang = $lang;
    }

    public static function getLang() {
        return self::$currentLang;
    }

    public static function loadTranslations($lang = null) {
        $lang = $lang ?? self::$currentLang;
        $filePath = DATA_ROOT . "/lang/{$lang}.json";
        
        if (!file_exists($filePath)) {
            $filePath = DATA_ROOT . '/lang/zh.json';
        }

        if (file_exists($filePath)) {
            self::$translations[$lang] = json_decode(file_get_contents($filePath), true) ?: [];
        } else {
            self::$translations[$lang] = [];
        }
    }

    public static function t($key, $params = []) {
        $lang = self::$currentLang;
        
        if (!isset(self::$translations[$lang])) {
            self::loadTranslations($lang);
        }

        $translations = self::$translations[$lang] ?? [];
        $value = $translations[$key] ?? $key;

        foreach ($params as $paramKey => $paramValue) {
            $value = str_replace("{{{$paramKey}}}", $paramValue, $value);
        }

        return $value;
    }

    public static function getAvailableLangs() {
        $langDir = DATA_ROOT . '/lang';
        if (!file_exists($langDir)) {
            mkdir($langDir, 0755, true);
            self::createDefaultLangFiles();
        }

        $files = glob($langDir . '/*.json');
        $langs = [];
        
        foreach ($files as $file) {
            $lang = basename($file, '.json');
            $langs[] = $lang;
        }

        return $langs;
    }

    public static function createDefaultLangFiles() {
        $zh = [
            'welcome' => '欢迎使用 FilePhpBox',
            'upload_file' => '上传文件',
            'upload_text' => '上传文本',
            'file_name' => '文件名',
            'file_size' => '文件大小',
            'expire_time' => '过期时间',
            'download_count' => '下载次数',
            'share_link' => '分享链接',
            'copy_link' => '复制链接',
            'delete' => '删除',
            'confirm_delete' => '确定删除此文件？',
            'admin_login' => '管理员登录',
            'password' => '密码',
            'login' => '登录',
            'logout' => '退出登录',
            'file_list' => '文件列表',
            'statistics' => '统计信息',
            'settings' => '设置',
            'logs' => '日志',
            'search' => '搜索',
            'page' => '第 {{page}} 页',
            'total' => '共 {{total}} 条',
            'save' => '保存',
            'success' => '操作成功',
            'error' => '操作失败',
            'not_found' => '未找到',
            'unauthorized' => '未授权',
            'forbidden' => '禁止访问',
            'file_upload_success' => '文件上传成功',
            'text_upload_success' => '文本上传成功',
            'file_deleted' => '文件已删除',
            'config_updated' => '配置已更新',
            'password_changed' => '密码已修改',
            'expired' => '已过期',
            'never_expire' => '永久有效',
            'views' => '访问次数',
            'storage_used' => '已使用存储',
            'storage_free' => '剩余空间',
            'today_uploads' => '今日上传',
            'total_files' => '文件总数',
            'clean_expired' => '清理过期文件',
            'export_data' => '导出数据',
            'batch_delete' => '批量删除',
            'select_all' => '全选',
            'deselect_all' => '取消全选',
            'delete_selected' => '删除选中'
        ];

        $en = [
            'welcome' => 'Welcome to FilePhpBox',
            'upload_file' => 'Upload File',
            'upload_text' => 'Upload Text',
            'file_name' => 'File Name',
            'file_size' => 'File Size',
            'expire_time' => 'Expire Time',
            'download_count' => 'Download Count',
            'share_link' => 'Share Link',
            'copy_link' => 'Copy Link',
            'delete' => 'Delete',
            'confirm_delete' => 'Confirm delete this file?',
            'admin_login' => 'Admin Login',
            'password' => 'Password',
            'login' => 'Login',
            'logout' => 'Logout',
            'file_list' => 'File List',
            'statistics' => 'Statistics',
            'settings' => 'Settings',
            'logs' => 'Logs',
            'search' => 'Search',
            'page' => 'Page {{page}}',
            'total' => 'Total {{total}}',
            'save' => 'Save',
            'success' => 'Success',
            'error' => 'Error',
            'not_found' => 'Not Found',
            'unauthorized' => 'Unauthorized',
            'forbidden' => 'Forbidden',
            'file_upload_success' => 'File uploaded successfully',
            'text_upload_success' => 'Text uploaded successfully',
            'file_deleted' => 'File deleted',
            'config_updated' => 'Config updated',
            'password_changed' => 'Password changed',
            'expired' => 'Expired',
            'never_expire' => 'Never expire',
            'views' => 'Views',
            'storage_used' => 'Storage Used',
            'storage_free' => 'Free Space',
            'today_uploads' => 'Today Uploads',
            'total_files' => 'Total Files',
            'clean_expired' => 'Clean Expired',
            'export_data' => 'Export Data',
            'batch_delete' => 'Batch Delete',
            'select_all' => 'Select All',
            'deselect_all' => 'Deselect All',
            'delete_selected' => 'Delete Selected'
        ];

        $langDir = DATA_ROOT . '/lang';
        if (!file_exists($langDir)) {
            mkdir($langDir, 0755, true);
        }

        file_put_contents($langDir . '/zh.json', json_encode($zh, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        file_put_contents($langDir . '/en.json', json_encode($en, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
