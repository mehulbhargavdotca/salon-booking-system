<?php
// Database Connection Class for Salon Booking System

require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn = null;
    
    // Get database connection
    public function getConnection() {
        if ($this->conn === null) {
            try {
                $this->conn = new PDO(
                    "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                    $this->username,
                    $this->password
                );
                
                // Set PDO attributes
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                
            } catch(PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw new Exception("Database connection failed");
            }
        }
        
        return $this->conn;
    }
    
    // Execute query with parameters
    public function query($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            throw new Exception("Database query failed");
        }
    }
    
    // Get single record
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    // Get multiple records
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    // Insert record and return last insert ID
    public function insert($sql, $params = []) {
        $this->query($sql, $params);
        return $this->getConnection()->lastInsertId();
    }
    
    // Update record and return affected rows
    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    // Delete record and return affected rows
    public function delete($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    // Begin transaction
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }
    
    // Commit transaction
    public function commit() {
        return $this->getConnection()->commit();
    }
    
    // Rollback transaction
    public function rollback() {
        return $this->getConnection()->rollback();
    }
    
    // Check if record exists
    public function exists($table, $conditions = []) {
        $sql = "SELECT 1 FROM {$table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $where[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $sql .= " LIMIT 1";
        $result = $this->fetch($sql, $params);
        return $result !== false;
    }
    
    // Get record count
    public function count($table, $conditions = []) {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        $params = [];
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $column => $value) {
                $where[] = "{$column} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        $result = $this->fetch($sql, $params);
        return $result ? (int)$result['count'] : 0;
    }
    
    // Close connection
    public function close() {
        $this->conn = null;
    }
}

// Create global database instance
$db = new Database();

// Helper function to get database instance
function getDB() {
    global $db;
    return $db;
}
?>