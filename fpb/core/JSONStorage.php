<?php

class JSONStorage {
    private $dataDir;

    public function __construct() {
        $this->dataDir = DATA_ROOT;
        if (!file_exists($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }
    }

    public function save($table, $data) {
        $filePath = $this->getFilePath($table);
        
        if (!file_exists($filePath)) {
            file_put_contents($filePath, json_encode([], JSON_PRETTY_PRINT));
        }

        $allData = json_decode(file_get_contents($filePath), true) ?: [];
        
        if (isset($data['id']) && $data['id'] > 0) {
            foreach ($allData as $key => $item) {
                if ($item['id'] == $data['id']) {
                    $allData[$key] = $data;
                    file_put_contents($filePath, json_encode($allData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                    return $data;
                }
            }
        }

        $data['id'] = $this->generateId($allData);
        $data['created_at'] = date('Y-m-d H:i:s');
        $allData[] = $data;
        
        file_put_contents($filePath, json_encode($allData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return $data;
    }

    public function find($table, $id) {
        $filePath = $this->getFilePath($table);
        
        if (!file_exists($filePath)) {
            return null;
        }

        $allData = json_decode(file_get_contents($filePath), true) ?: [];
        
        foreach ($allData as $item) {
            if ($item['id'] == $id) {
                return $item;
            }
        }
        
        return null;
    }

    public function findBy($table, $column, $value) {
        $filePath = $this->getFilePath($table);
        
        if (!file_exists($filePath)) {
            return null;
        }

        $allData = json_decode(file_get_contents($filePath), true) ?: [];
        
        foreach ($allData as $item) {
            if (isset($item[$column]) && $item[$column] == $value) {
                return $item;
            }
        }
        
        return null;
    }

    public function findAll($table, $limit = null, $offset = 0) {
        $filePath = $this->getFilePath($table);
        
        if (!file_exists($filePath)) {
            return [];
        }

        $allData = json_decode(file_get_contents($filePath), true) ?: [];
        
        usort($allData, function($a, $b) {
            return strtotime($b['created_at'] ?? '') - strtotime($a['created_at'] ?? '');
        });
        
        if ($limit !== null) {
            return array_slice($allData, $offset, $limit);
        }
        
        return $allData;
    }

    public function count($table) {
        $filePath = $this->getFilePath($table);
        
        if (!file_exists($filePath)) {
            return 0;
        }

        $allData = json_decode(file_get_contents($filePath), true) ?: [];
        return count($allData);
    }

    public function delete($table, $id) {
        $filePath = $this->getFilePath($table);
        
        if (!file_exists($filePath)) {
            return false;
        }

        $allData = json_decode(file_get_contents($filePath), true) ?: [];
        $newData = [];
        
        foreach ($allData as $item) {
            if ($item['id'] != $id) {
                $newData[] = $item;
            }
        }
        
        file_put_contents($filePath, json_encode($newData, JSON_PRETTY_PRINT));
        return true;
    }

    public function exists($table, $column, $value) {
        $filePath = $this->getFilePath($table);
        
        if (!file_exists($filePath)) {
            return false;
        }

        $allData = json_decode(file_get_contents($filePath), true) ?: [];
        
        foreach ($allData as $item) {
            if (isset($item[$column]) && $item[$column] == $value) {
                return true;
            }
        }
        
        return false;
    }

    public function update($table, $id, $data) {
        $filePath = $this->getFilePath($table);
        
        if (!file_exists($filePath)) {
            return false;
        }

        $allData = json_decode(file_get_contents($filePath), true) ?: [];
        $updated = false;
        
        foreach ($allData as &$item) {
            if ($item['id'] == $id) {
                $item = array_merge($item, $data);
                $updated = true;
                break;
            }
        }
        
        if ($updated) {
            file_put_contents($filePath, json_encode($allData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }
        
        return $updated;
    }

    private function getFilePath($table) {
        return $this->dataDir . '/' . $table . '.json';
    }

    private function generateId($data) {
        $maxId = 0;
        foreach ($data as $item) {
            if (isset($item['id']) && $item['id'] > $maxId) {
                $maxId = $item['id'];
            }
        }
        return $maxId + 1;
    }
}

$jsonStorage = new JSONStorage();
