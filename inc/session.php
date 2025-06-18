<?php
// session.php

// Include database connection
include_once 'conn.php';


// Start session
session_start();

// Initialize database connection
$pdo = new Database();

// Existing session logic (e.g., session_start(), conn.php)
$page_id = isset($page_id) ? $page_id : 'default'; // Default page_id
require_once 'includes/realtime.php'; // Path to realtime.php

// Visitor tracking logic (logs every page visit)
try {
    // Get visitor data
    $page_name = basename($_SERVER['PHP_SELF']); // Current page name (e.g., index.php)
    $visit_time = date('Y-m-d H:i:s');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown IP';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown UA';

    // Generate or retrieve visitor_id
    if (!isset($_COOKIE['visitor_id'])) {
        $visitor_id = bin2hex(random_bytes(16)); // Generate a 32-character unique ID
        setcookie('visitor_id', $visitor_id, time() + (365 * 24 * 60 * 60), "/", "", true, true); // 1-year cookie, Secure, HttpOnly
    } else {
        $visitor_id = $_COOKIE['visitor_id'];
    }

    // Get approximate location using ip-api.com
    $location = 'Location not available';
    $context = stream_context_create(['http' => ['timeout' => 5]]);
    $geolocation = @json_decode(file_get_contents("http://ip-api.com/json/{$ip_address}", false, $context));
    if ($geolocation && $geolocation->status === 'success') {
        $location = "{$geolocation->city}, {$geolocation->regionName}, {$geolocation->country}";
    }

    // Get user ID if logged in
    $user_id = isset($_SESSION['user']) ? $_SESSION['user'] : null;

    // Open database connection
    $conn = $pdo->open();

    // Prepare and execute INSERT query
    $stmt = $conn->prepare("INSERT INTO visitor_logs (visitor_id, page_name, visit_time, location, ip_address, user_id, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$visitor_id, $page_name, $visit_time, $location, $ip_address, $user_id, $user_agent]);

    // Close connection
    $pdo->close();

    // Optional: Log success (remove in production if not needed)
    error_log("Visitor data logged successfully for page: $page_name, visitor_id: $visitor_id", 3, "visitor.log");
} catch (Exception $e) {
    error_log("Error logging visitor: " . $e->getMessage(), 3, "errors.log");
    // Continue execution even if logging fails
}

// Session management logic
if (isset($_SESSION['admin'])) {
    header('location: admin/home.php');
    exit();
}

if (isset($_SESSION['user'])) {
    try {
        $conn = $pdo->open();

        $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['user']]);
        $user = $stmt->fetch();

        $pdo->close();
    } catch (PDOException $e) {
        error_log("Connection error: " . $e->getMessage(), 3, "errors.log");
        // Optionally, handle the error (e.g., unset session or redirect)
    }
}
?>
