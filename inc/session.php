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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
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
            transition: background-color 0.3s, transform 0.3s;
            pointer-events: auto; /* Ensure the button is interactive */
        }

        .livechat-button:hover {
            background-color: #45a049; /* Darker green on hover */
            transform: scale(1.1); /* Slight zoom effect */
        }

        .livechat-button i {
            font-size: 30px; /* Adjust icon size */
        }

        /* Tooltip Styling */
        .livechat-button .tooltip {
            visibility: hidden;
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 8px 12px;
            border-radius: 6px;
            position: absolute;
            z-index: 1001; /* Ensure tooltip is above other elements */
            bottom: 70px; /* Position above the button */
            right: 50%; /* Center horizontally relative to button */
            transform: translateX(50%); /* Adjust for centering */
            white-space: nowrap;
            font-size: 14px;
            font-family: Arial, sans-serif; /* Fallback font */
            opacity: 0;
            transition: opacity 0.3s, visibility 0.3s;
            /* Debug: Add border to make tooltip visible during testing */
            border: 1px solid #fff;
        }

        .livechat-button:hover .tooltip {
            visibility: visible;
            opacity: 1;
        }

        /* Responsive design for smaller screens */
        @media (max-width: 768px) {
            .livechat-button {
                width: 50px;
                height: 50px;
                bottom: 10px;
                right: 10px;
            }

            .livechat-button i {
                font-size: 25px; /* Adjust icon size for smaller screens */
            }

            .livechat-button .tooltip {
                bottom: 60px; /* Adjust tooltip position for smaller screens */
                font-size: 12px; /* Smaller font size for mobile */
            }
        }
    </style>
</head>
<body>
    <!-- Floating Live Chat Button -->
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ($current_page !== 'livechat.php' && !str_ends_with($current_path, '/accounts/livechat.php')) {
    ?>
        <a href="livechat.php" class="livechat-button">
            <i class="fas fa-headset"></i>
            <span class="tooltip">Need Help?</span>
        </a>
    <?php } ?>
</body>
</html>
