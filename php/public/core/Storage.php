<?php

class Storage {
    private static $storages = [];

    public static function getStorage($type = null) {
        global $settings;
        $type = $type ?? $settings['file_storage'];

        if (!isset(self::$storages[$type])) {
            switch ($type) {
                case 'local':
                    self::$storages[$type] = new LocalStorage();
                    break;
                case 's3':
                    self::$storages[$type] = new S3Storage();
                    break;
                case 'onedrive':
                    self::$storages[$type] = new OneDriveStorage();
                    break;
                default:
                    self::$storages[$type] = new LocalStorage();
            }
        }

        return self::$storages[$type];
    }
}

class LocalStorage {
    private $chunkSize = 256 * 1024;
    private $rootPath;

    public function __construct() {
        $this->rootPath = DATA_ROOT;
    }

    public function saveFile($file, $savePath) {
        $fullPath = $this->rootPath . '/' . $savePath;
        $dir = dirname($fullPath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $content = file_get_contents($file['tmp_name']);
        file_put_contents($fullPath, $content);
    }

    public function deleteFile($fileCode) {
        $fullPath = $this->rootPath . '/' . $fileCode->getFilePath();
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    public function getFileUrl($fileCode) {
        return Utils::getFileUrl($fileCode->code);
    }

    public function getFileResponse($fileCode) {
        $fullPath = $this->rootPath . '/' . $fileCode->getFilePath();
        if (!file_exists($fullPath)) {
            Response::notFound('文件已过期删除');
        }

        $filename = $fileCode->prefix . $fileCode->suffix;
        $content = file_get_contents($fullPath);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }

    public function getFileContent($fileCode) {
        $fullPath = $this->rootPath . '/' . $fileCode->getFilePath();
        if (!file_exists($fullPath)) {
            return null;
        }
        return file_get_contents($fullPath);
    }
}

class S3Storage {
    public function saveFile($file, $savePath) {
        Response::forbidden('S3存储暂未实现');
    }

    public function deleteFile($fileCode) {
        Response::forbidden('S3存储暂未实现');
    }

    public function getFileUrl($fileCode) {
        Response::forbidden('S3存储暂未实现');
    }

    public function getFileResponse($fileCode) {
        Response::forbidden('S3存储暂未实现');
    }
}

class OneDriveStorage {
    public function saveFile($file, $savePath) {
        Response::forbidden('OneDrive存储暂未实现');
    }

    public function deleteFile($fileCode) {
        Response::forbidden('OneDrive存储暂未实现');
    }

    public function getFileUrl($fileCode) {
        Response::forbidden('OneDrive存储暂未实现');
    }

    public function getFileResponse($fileCode) {
        Response::forbidden('OneDrive存储暂未实现');
    }
}
