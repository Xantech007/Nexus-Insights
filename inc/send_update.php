<?php
header('Content-Type: application/json');
require_once 'conn.php'; // Your PDO connection
require_once 'vendor/autoload.php'; // Pusher SDK
use Pusher\Pusher;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_id = $_POST['page_id'] ?? '';
    $content = $_POST['content'] ?? '';

    // Sanitize input to prevent malicious code
    $content = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');

    // Limit page_id to valid values
    $allowed_pages = ['chat', 'home', 'dashboard']; // Add your page IDs
    if (!in_array($page_id, $allowed_pages)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid page ID']);
        exit;
    }

    if ($page_id && $content) {
        try {
            // Save to database
            $stmt = $conn->prepare('INSERT INTO updates (page_id, content, timestamp) VALUES (?, ?, NOW())');
            $stmt->execute([$page_id, $content]);

            // Send to Pusher
            $pusher = new Pusher(
                'ebd414bd7136b6d89caf', // Pusher key
                '6da1dd54bcbab2381c9b', // Pusher secret
                '2010039', // Pusher app_id
                ['eu' => 'your_cluster'] // Pusher cluster
            );
            $pusher->trigger('page-channel-' . $page_id, 'new-update', ['content' => $content]);

            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            error_log('Error: ' . $e->getMessage(), 3, 'errors.log');
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Missing page ID or content']);
    }
}
?>
