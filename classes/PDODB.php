<?php

class PDODB {

    private $pdo;
    private $dbAlias = '';
    private $realDBName = '';
    private $rowCount;
    // store instances per dbAlias
    private static $instances = [];

    // private constructor: can't be called from outside directly
    private function __construct($name) {

        $config = GetAllConfig::load(); //from config.ini at top level

        $cfg = $config[$name];
        $dsn = "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset=utf8";
        $opt = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ];

        try {
            $this->pdo = new PDO($dsn, $cfg['user'], $cfg['password'], $opt);
            $this->pdo->exec("SET SESSION sql_mode = 'NO_ZERO_DATE,NO_ZERO_IN_DATE'");
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed.");
        }
        $this->dbAlias = $name;
        $this->realDBName = $cfg['dbname'];
    }

    /**
     * Static factory method to get the instance
     */
    public static function getInstance($name): self {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new self($name);
        }
        return self::$instances[$name];
    }

    public static function getRealDBName($alias) {

        $config = GetAllConfig::load(); //from config.ini at top level
        $cfg = $config[$alias];
        return $cfg['dbname'];
    }

    public function getPDO() {
        return $this->pdo;
    }

    public function listFields($table, $and = '') {
        try {

            $query = "
            SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE, COLUMN_DEFAULT
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? $and 
            ORDER BY ORDINAL_POSITION";
            return $this->queryFetch($query, [$table]);
        } catch (PDOException $e) {
            error_log("Failed to fetch table fields: " . $e->getMessage());
            throw new Exception("Failed to fetch table fields.");
        }
    }

    public function queryPrepare($sql) {
        if ($sql instanceof PDOStatement) {
            return $sql;
        }
        try {
            return $this->pdo->prepare($sql);
        } catch (PDOException $e) {
            error_log("Prepare fro $sql failed: " . $e->getMessage());
            throw new Exception("Query execution failed.");
        }
    }

    public function prepare($q) { // convinience
        return $this->queryPrepare($q);
    }

    public function queryFetchOne($sql, $params = []) {
        if (is_string($sql) && $this->hasLimitAtEnd($sql) === false) {
            $sql .= " limit 1";
        }
        $r = $this->query($sql, $params);
        return isset($r[0]) ? $r[0] : '';
    }

    public function queryFetch($sql, $params = []) {
        return $this->query($sql, $params);
    }

    public function queryOther($sql, $params = []) {
        $this->query($sql, $params);
        return $this->rowCount;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->isSqlOrStatement($sql);
            $stmt->execute($params);
            $r = $stmt->fetchAll();
            $this->rowCount = $stmt->rowCount();
            if ($r) {
                return $r;
            }
            return [];
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage());
            throw new Exception("Query execution failed.");
        }
    }

    function getEmptyRecord(string $table): object {
        $stmt = $this->pdo->prepare("SHOW COLUMNS FROM `$table`");
        $stmt->execute();

        $record = new stdClass();

        while ($column = $stmt->fetchObject()) {
            $field = $column->Field;
            $type = strtolower($column->Type);
            if (strpos($column->Extra, 'auto_increment') !== false) {
                $record->$field = null;
            } elseif (preg_match('/int|float|double|decimal/', $type)) {
                $record->$field = 0;
            } elseif (preg_match('/date|time/', $type)) {
                $record->$field = null;
            } else {
                $record->$field = '';
            }
        }

        return $record;
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }

    private function hasLimitAtEnd($sql) {
        return preg_match('/\bLIMIT\s+[0-9]+(?:\s*,\s*[0-9]+)?\s*;?\s*$/i', $sql) === 1;
    }

    private function isSqlOrStatement($sql) {
        if ($sql instanceof PDOStatement) {
            $stmt = $sql;
        } else {
            try {
                $stmt = $this->pdo->prepare($sql);
            } catch (PDOException $e) {
                error_log("Prepare for $sql failed: " . $e->getMessage());
                throw new Exception("Query execution failed.");
            }
        }
        return $stmt;
    }
}
