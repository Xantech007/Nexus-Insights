<?php

class Database {
 
	private $server = "mysql:host=localhost;dbname=nexuymmv_db";
	private $username = "nexuymmv_db";
	private $password = "Xander24427279";
	private $options  = array(
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	);
	protected $conn;
 	
	public function open(){
 		try {
 			$this->conn = new PDO($this->server, $this->username, $this->password, $this->options);
 			return $this->conn;
 		} catch (PDOException $e) {
 			// Log error to a file
 			error_log("Database connection error: " . $e->getMessage() . "\n", 3, __DIR__ . "/error_log.txt");
 			die("Database connection failed.");
 		}
    }
 
	public function close(){
   		$this->conn = null;
 	}
}

$pdo = new Database();

?>
