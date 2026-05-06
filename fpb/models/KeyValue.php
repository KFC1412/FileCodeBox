<?php

require_once __DIR__ . '/../core/JSONStorage.php';

class KeyValue {
    public $id;
    public $key;
    public $value;
    public $created_at;

    public static function get($key) {
        global $jsonStorage;
        $data = $jsonStorage->findBy('key_value', 'key', $key);
        if ($data) {
            $kv = new self();
            foreach ($data as $k => $v) {
                $kv->$k = $v;
            }
            return $kv;
        }
        return null;
    }

    public static function set($key, $value) {
        global $jsonStorage;
        $data = $jsonStorage->findBy('key_value', 'key', $key);
        
        $saveData = [
            'key' => $key,
            'value' => is_string($value) ? $value : json_encode($value),
        ];
        
        if ($data) {
            $saveData['id'] = $data['id'];
        }
        
        $jsonStorage->save('key_value', $saveData);
    }

    public static function getValue($key, $default = null) {
        $kv = self::get($key);
        if ($kv) {
            $decoded = json_decode($kv->value, true);
            return $decoded !== null ? $decoded : $kv->value;
        }
        return $default;
    }
}
