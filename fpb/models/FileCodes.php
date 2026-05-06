<?php

require_once __DIR__ . '/../core/JSONStorage.php';

class FileCodes {
    public $id;
    public $code;
    public $prefix;
    public $suffix;
    public $uuid_file_name;
    public $file_path;
    public $size;
    public $text;
    public $expired_at;
    public $expired_count;
    public $used_count;
    public $created_at;

    public static function findByCode($code) {
        global $jsonStorage;
        $data = $jsonStorage->findBy('file_codes', 'code', $code);
        return self::createFromData($data);
    }

    public static function findById($id) {
        global $jsonStorage;
        $data = $jsonStorage->find('file_codes', $id);
        return self::createFromData($data);
    }

    public static function all($limit = null, $offset = 0) {
        global $jsonStorage;
        $dataList = $jsonStorage->findAll('file_codes', $limit, $offset);
        $result = [];
        foreach ($dataList as $data) {
            $fileCode = self::createFromData($data);
            if ($fileCode) {
                $result[] = $fileCode;
            }
        }
        return $result;
    }

    public static function count() {
        global $jsonStorage;
        return $jsonStorage->count('file_codes');
    }

    public static function create($data) {
        global $jsonStorage;
        $saveData = [
            'code' => $data['code'],
            'prefix' => $data['prefix'] ?? '',
            'suffix' => $data['suffix'] ?? '',
            'uuid_file_name' => $data['uuid_file_name'] ?? null,
            'file_path' => $data['file_path'] ?? null,
            'size' => $data['size'] ?? 0,
            'text' => $data['text'] ?? null,
            'expired_at' => $data['expired_at'] ?? null,
            'expired_count' => $data['expired_count'] ?? 0,
            'used_count' => $data['used_count'] ?? 0,
        ];
        
        $saved = $jsonStorage->save('file_codes', $saveData);
        return self::createFromData($saved);
    }

    public function save() {
        global $jsonStorage;
        $data = [
            'id' => $this->id,
            'code' => $this->code,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'uuid_file_name' => $this->uuid_file_name,
            'file_path' => $this->file_path,
            'size' => $this->size,
            'text' => $this->text,
            'expired_at' => $this->expired_at,
            'expired_count' => $this->expired_count,
            'used_count' => $this->used_count,
            'created_at' => $this->created_at,
        ];
        $jsonStorage->save('file_codes', $data);
    }

    public function delete() {
        global $jsonStorage;
        $jsonStorage->delete('file_codes', $this->id);
    }

    public static function exists($code) {
        global $jsonStorage;
        return $jsonStorage->exists('file_codes', 'code', $code);
    }

    public function isExpired() {
        if ($this->expired_at === null || $this->expired_at === '') {
            return false;
        }

        if ($this->expired_count < 0) {
            $expiredAt = strtotime($this->expired_at);
            return $expiredAt < time();
        }

        return $this->expired_count <= 0;
    }

    public function getFilePath() {
        return $this->file_path . '/' . $this->uuid_file_name;
    }

    public function toArray() {
        return [
            'id' => intval($this->id),
            'code' => $this->code,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'uuid_file_name' => $this->uuid_file_name,
            'file_path' => $this->file_path,
            'size' => intval($this->size),
            'text' => $this->text,
            'expired_at' => $this->expired_at,
            'expired_count' => intval($this->expired_count),
            'used_count' => intval($this->used_count),
            'created_at' => $this->created_at,
        ];
    }

    private static function createFromData($data) {
        if (!$data) {
            return null;
        }
        $fileCode = new self();
        foreach ($data as $key => $value) {
            $fileCode->$key = $value;
        }
        return $fileCode;
    }
}
