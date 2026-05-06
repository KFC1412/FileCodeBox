<?php

class FileSecurity {
    private static $allowedExtensions = [
        'image' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'],
        'document' => ['txt', 'md', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'],
        'archive' => ['zip', 'rar', '7z', 'tar', 'gz'],
        'code' => ['php', 'js', 'html', 'css', 'json', 'xml', 'py', 'java', 'cpp'],
        'audio' => ['mp3', 'wav', 'ogg', 'flac'],
        'video' => ['mp4', 'webm', 'ogg', 'mov']
    ];

    private static $blockedExtensions = ['exe', 'com', 'bat', 'cmd', 'msi', 'dll', 'scr', 'pif', 'sys'];
    private static $blockedMimeTypes = [
        'application/x-msdownload',
        'application/x-executable',
        'application/x-dosexec',
        'application/x-bat',
        'application/x-cmd'
    ];

    public static function setAllowedExtensions($extensions) {
        self::$allowedExtensions = $extensions;
    }

    public static function isAllowedExtension($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, self::$blockedExtensions)) {
            return false;
        }

        foreach (self::$allowedExtensions as $category => $exts) {
            if (in_array($ext, $exts)) {
                return true;
            }
        }

        return false;
    }

    public static function getExtensionCategory($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        foreach (self::$allowedExtensions as $category => $exts) {
            if (in_array($ext, $exts)) {
                return $category;
            }
        }

        return 'other';
    }

    public static function isBlockedMimeType($mimeType) {
        return in_array($mimeType, self::$blockedMimeTypes);
    }

    public static function sanitizeFilename($filename) {
        $filename = preg_replace('/[^\w\.\-]/u', '_', $filename);
        $filename = trim($filename, '.');
        return $filename;
    }

    public static function validateFileSize($filesize, $maxSize) {
        return $filesize <= $maxSize;
    }
}

class FileEncryption {
    private static $key;
    private static $ivLength = openssl_cipher_iv_length('aes-256-cbc');

    public static function setKey($key) {
        self::$key = hash('sha256', $key, true);
    }

    public static function encrypt($data) {
        if (!self::$key) {
            self::setKey('filephpbox_default_key');
        }

        $iv = openssl_random_pseudo_bytes(self::$ivLength);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', self::$key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt($data) {
        if (!self::$key) {
            self::setKey('filephpbox_default_key');
        }

        $data = base64_decode($data);
        $iv = substr($data, 0, self::$ivLength);
        $encrypted = substr($data, self::$ivLength);
        return openssl_decrypt($encrypted, 'aes-256-cbc', self::$key, OPENSSL_RAW_DATA, $iv);
    }

    public static function encryptFile($inputFile, $outputFile) {
        if (!self::$key) {
            self::setKey('filephpbox_default_key');
        }

        $iv = openssl_random_pseudo_bytes(self::$ivLength);
        $data = file_get_contents($inputFile);
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', self::$key, OPENSSL_RAW_DATA, $iv);
        file_put_contents($outputFile, $iv . $encrypted);
        return true;
    }

    public static function decryptFile($inputFile, $outputFile) {
        if (!self::$key) {
            self::setKey('filephpbox_default_key');
        }

        $data = file_get_contents($inputFile);
        $iv = substr($data, 0, self::$ivLength);
        $encrypted = substr($data, self::$ivLength);
        $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', self::$key, OPENSSL_RAW_DATA, $iv);
        file_put_contents($outputFile, $decrypted);
        return true;
    }

    public static function generateKey() {
        return bin2hex(random_bytes(32));
    }
}
