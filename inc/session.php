<?php
// session.php

// Include database connection
include_once 'conn.php';


// Start session
session_start();

// Initialize database connection
$pdo = new Database();

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


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Floating Live Chat Button Styling */
        .livechat-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            background-color: #4CAF50; /* Green background */
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            font-size: 24px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .livechat-button:hover {
            background-color: #45a049; /* Darker green on hover */
            transform: scale(1.1); /* Slight zoom effect */
        }

        .livechat-button img {
            width: 30px;
            height: 30px;
        }

        /* Responsive design for smaller screens */
        @media (max-width: 768px) {
            .livechat-button {
                width: 50px;
                height: 50px;
                bottom: 10px;
                right: 10px;
            }

            .livechat-button img {
                width: 25px;
                height: 25px;
            }
        }
    </style>
</head>
<body>
    <!-- Floating Live Chat Button -->
    <a href="livechat.php" class="livechat-button" title="Open Live Chat">
        <!-- You can use an icon (e.g., a chat bubble image or Font Awesome icon) -->
        <img src="path/to/chat-icon.png" alt="Chat Icon">
        <!-- Alternatively, use a text symbol if no image is available -->
        <!-- 💬 -->
    </a>
</body>
    </html>
