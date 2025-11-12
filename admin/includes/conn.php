<?php
class Database {
    private $server = "mysql:host=localhost;dbname=nexuymmv_db";
    private $username = "nexuymmv_db";
    private $password = "Xander24427279";
    private $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    protected $conn;

    public function open() {
        try {
            $this->conn = new PDO($this->server, $this->username, $this->password, $this->options);
            return $this->conn;
        } catch (PDOException $e) {
            // Log the error instead of echoing it in production
            error_log("Connection failed: " . $e->getMessage());
            return null; // Return null on failure to allow graceful error handling
        }
    }

    public function close() {
        $this->conn = null;
    }
}

$pdo = new Database();
?>
