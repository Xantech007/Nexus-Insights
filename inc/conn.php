<?php
class Database {
    private $server = "mysql:host=localhost;dbname=nexuymmv_db;charset=utf8mb4";
    private $username = "nexuymmv_db"; // Updated username
    private $password = "Xander24427279"; // Updated password
    private $options = array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    );
    protected $conn;

    public function open() {
        try {
            $this->conn = new PDO($this->server, $this->username, $this->password, $this->options);
            return $this->conn;
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage() . "\n", 3, __DIR__ . "/error_log.txt");
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function close() {
        $this->conn = null;
    }
}
?>
