<?php
class Database {
    private static $instance = null;
    protected $db;

    function connect() {
        try {
            if ($this->db === null) {
                $this->db = new PDO("mysql:host=localhost;dbname=pms", "root", "");
                $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            }
            return $this->db;
        } catch(PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    // Singleton pattern to ensure we're using the same connection
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
?>
