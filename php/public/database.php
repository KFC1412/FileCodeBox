<?php

class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (self::$pdo === null) {
            $dbPath = DATA_ROOT . '/filecodebox.db';
            try {
                self::$pdo = new PDO("sqlite:$dbPath");
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::initTables();
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['code' => 500, 'detail' => '数据库连接失败']);
                exit;
            }
        }
        return self::$pdo;
    }

    private static function initTables() {
        $pdo = self::$pdo;
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS file_codes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT UNIQUE NOT NULL,
                prefix TEXT DEFAULT '',
                suffix TEXT DEFAULT '',
                uuid_file_name TEXT,
                file_path TEXT,
                size INTEGER DEFAULT 0,
                text TEXT,
                expired_at TEXT,
                expired_count INTEGER DEFAULT 0,
                used_count INTEGER DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS key_value (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                key TEXT UNIQUE NOT NULL,
                value TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $pdo->exec("
            CREATE INDEX IF NOT EXISTS idx_file_codes_code ON file_codes(code)
        ");
        $pdo->exec("
            CREATE INDEX IF NOT EXISTS idx_key_value_key ON key_value(key)
        ");
    }
}
