<?php

require_once BASE_DIR . '/database.php';

class KeyValue {
    public $id;
    public $key;
    public $value;
    public $created_at;

    public static function get($key) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM key_value WHERE `key` = ? LIMIT 1");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $kv = new self();
            foreach ($result as $k => $v) {
                $kv->$k = $v;
            }
            return $kv;
        }
        return null;
    }

    public static function set($key, $value) {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO key_value (`key`, `value`, created_at)
            VALUES (:key, :value, datetime('now', '+8 hours'))
            ON CONFLICT(`key`) DO UPDATE SET `value` = :value2
        ");
        $valueJson = is_string($value) ? $value : json_encode($value);
        $stmt->execute([':key' => $key, ':value' => $valueJson, ':value2' => $valueJson]);
    }
}
