<?php
/**
 * Database Connection Class for Pastimes Clothing Store
 * Student Numbers: ST10451774 & ST10452404
 * Names: Acazia Ammon & Masike Jr Rasenyalo
 * Declaration: This code is our own work except where referenced
 * 
 * Database connection using MySQLi with prepared statements
 * Following PHP.net (2022) best practices for secure database connections
 */

class DBConn {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "ClothingStore";
    private $conn;
    
    /**
     * Constructor - Establish database connection
     * Implements singleton pattern for connection management
     */
    public function __construct() {
        $this->connect();
    }
    
    /**
     * Establish database connection
     * Uses MySQLi extension for improved security over deprecated MySQL
     */
    private function connect() {
        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            // Set charset to prevent SQL injection issues
            $this->conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    /**
     * Get database connection
     * @return mysqli connection object
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Execute prepared statement
     * @param string $sql - SQL query with placeholders
     * @param string $types - Type definitions (i, s, d, b)
     * @param array $params - Parameters to bind
     * @return mysqli_stmt|false
     */
    public function prepareAndExecute($sql, $types = "", $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            
            if ($stmt === false) {
                throw new Exception("Prepare failed: " . $this->conn->error);
            }
            
            if (!empty($types) && !empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            return $stmt;
            
        } catch (Exception $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get multiple rows from query
     * @param mysqli_stmt|false $stmt
     * @return array
     */
    public function getMultipleRows($stmt) {
        if ($stmt === false) {
            return [];
        }
        
        $result = $stmt->get_result();
        if ($result === false) {
            return [];
        }
        
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get single row from query
     * @param mysqli_stmt|false $stmt
     * @return array|null
     */
    public function getSingleRow($stmt) {
        if ($stmt === false) {
            return null;
        }
        
        $result = $stmt->get_result();
        if ($result === false) {
            return null;
        }
        
        return $result->fetch_assoc();
    }
    
    /**
     * Get last insert ID
     * @return int
     */
    public function getLastInsertId() {
        return $this->conn->insert_id;
    }
    
    /**
     * Close database connection
     */
    public function close() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
    
    /**
     * Destructor - Close connection
     */
    public function __destruct() {
        $this->close();
    }
}
?>
