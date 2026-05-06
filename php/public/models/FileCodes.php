<?php

require_once BASE_DIR . '/database.php';

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
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM file_codes WHERE code = ? LIMIT 1");
        $stmt->execute([$code]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $fileCode = new self();
            foreach ($result as $key => $value) {
                $fileCode->$key = $value;
            }
            return $fileCode;
        }
        return null;
    }

    public static function findById($id) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM file_codes WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $fileCode = new self();
            foreach ($result as $key => $value) {
                $fileCode->$key = $value;
            }
            return $fileCode;
        }
        return null;
    }

    public static function all($limit = null, $offset = 0) {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM file_codes ORDER BY created_at DESC";
        if ($limit !== null) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $fileCodes = [];
        foreach ($results as $result) {
            $fileCode = new self();
            foreach ($result as $key => $value) {
                $fileCode->$key = $value;
            }
            $fileCodes[] = $fileCode;
        }
        return $fileCodes;
    }

    public static function count() {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) FROM file_codes");
        return intval($stmt->fetchColumn());
    }

    public static function create($data) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO file_codes (code, prefix, suffix, uuid_file_name, file_path, size, text, expired_at, expired_count, used_count, created_at)
            VALUES (:code, :prefix, :suffix, :uuid_file_name, :file_path, :size, :text, :expired_at, :expired_count, :used_count, datetime('now', '+8 hours'))
        ");
        $stmt->execute([
            ':code' => $data['code'],
            ':prefix' => $data['prefix'] ?? '',
            ':suffix' => $data['suffix'] ?? '',
            ':uuid_file_name' => $data['uuid_file_name'] ?? null,
            ':file_path' => $data['file_path'] ?? null,
            ':size' => $data['size'] ?? 0,
            ':text' => $data['text'] ?? null,
            ':expired_at' => $data['expired_at'] ?? null,
            ':expired_count' => $data['expired_count'] ?? 0,
            ':used_count' => $data['used_count'] ?? 0,
        ]);
        $id = $pdo->lastInsertId();
        return self::findById($id);
    }

    public function save() {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE file_codes SET
                prefix = :prefix,
                suffix = :suffix,
                uuid_file_name = :uuid_file_name,
                file_path = :file_path,
                size = :size,
                text = :text,
                expired_at = :expired_at,
                expired_count = :expired_count,
                used_count = :used_count
            WHERE id = :id
        ");
        $stmt->execute([
            ':id' => $this->id,
            ':prefix' => $this->prefix,
            ':suffix' => $this->suffix,
            ':uuid_file_name' => $this->uuid_file_name,
            ':file_path' => $this->file_path,
            ':size' => $this->size,
            ':text' => $this->text,
            ':expired_at' => $this->expired_at,
            ':expired_count' => $this->expired_count,
            ':used_count' => $this->used_count,
        ]);
    }

    public function delete() {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM file_codes WHERE id = ?");
        $stmt->execute([$this->id]);
    }

    public function isExpired() {
        return Utils::isExpired([
            'expired_at' => $this->expired_at,
            'expired_count' => $this->expired_count
        ]);
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
}
