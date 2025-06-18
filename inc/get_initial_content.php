<?php
header('Content-Type: application/json');
require_once 'conn.php'; // Your PDO connection

$page_id = $_GET['page_id'] ?? '';
try {
    $stmt = $conn->prepare('SELECT content FROM updates WHERE page_id = ? ORDER BY timestamp DESC LIMIT 10');
    $stmt->execute([$page_id]);
    $updates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($updates);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage(), 3, 'errors.log');
    echo json_encode(['error' => 'Database error']);
}
?>
