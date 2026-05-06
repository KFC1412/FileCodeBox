<?php

class FilePreview {
    private static $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
    private static $textExtensions = ['txt', 'md', 'json', 'xml', 'html', 'css', 'js', 'php', 'py'];

    public static function canPreview($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, array_merge(self::$imageExtensions, self::$textExtensions));
    }

    public static function getPreviewType($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, self::$imageExtensions)) {
            return 'image';
        }
        
        if (in_array($ext, self::$textExtensions)) {
            return 'text';
        }

        return 'download';
    }

    public static function getImagePreview($filePath, $maxWidth = 800, $maxHeight = 600) {
        if (!file_exists($filePath)) {
            return null;
        }

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                $source = imagecreatefromjpeg($filePath);
                break;
            case 'png':
                $source = imagecreatefrompng($filePath);
                break;
            case 'gif':
                $source = imagecreatefromgif($filePath);
                break;
            case 'webp':
                $source = imagecreatefromwebp($filePath);
                break;
            case 'bmp':
                $source = imagecreatefrombmp($filePath);
                break;
            default:
                return null;
        }

        if (!$source) {
            return null;
        }

        list($width, $height) = getimagesize($filePath);

        $ratio = $width / $height;
        if ($maxWidth / $maxHeight > $ratio) {
            $newHeight = $maxHeight;
            $newWidth = $newHeight * $ratio;
        } else {
            $newWidth = $maxWidth;
            $newHeight = $newWidth / $ratio;
        }

        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        ob_start();
        switch ($ext) {
            case 'png':
                imagepng($newImage);
                break;
            case 'gif':
                imagegif($newImage);
                break;
            case 'webp':
                imagewebp($newImage);
                break;
            default:
                imagejpeg($newImage);
        }
        $imageData = ob_get_clean();
        imagedestroy($source);
        imagedestroy($newImage);

        return 'data:image/' . ($ext === 'jpg' ? 'jpeg' : $ext) . ';base64,' . base64_encode($imageData);
    }

    public static function getTextPreview($filePath, $maxLength = 5000) {
        if (!file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        
        if (mb_strlen($content, 'UTF-8') > $maxLength) {
            $content = mb_substr($content, 0, $maxLength, 'UTF-8') . '...';
        }

        return $content;
    }

    public static function getFileIcon($filename) {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $icons = [
            'jpg' => 'image', 'jpeg' => 'image', 'png' => 'image', 'gif' => 'image', 'webp' => 'image', 'svg' => 'image',
            'pdf' => 'pdf',
            'doc' => 'doc', 'docx' => 'doc',
            'xls' => 'spreadsheet', 'xlsx' => 'spreadsheet',
            'ppt' => 'presentation', 'pptx' => 'presentation',
            'zip' => 'archive', 'rar' => 'archive', '7z' => 'archive',
            'txt' => 'text', 'md' => 'text',
            'json' => 'code', 'xml' => 'code', 'html' => 'code', 'css' => 'code', 'js' => 'code', 'php' => 'code',
            'mp3' => 'audio', 'wav' => 'audio',
            'mp4' => 'video', 'webm' => 'video'
        ];

        return $icons[$ext] ?? 'file';
    }
}
