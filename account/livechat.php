<?php
include('../inc/config.php');
include('../admin/includes/format.php');
include('../inc/session.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php'; // PHPMailer dependency

$id = $_SESSION['user'];

// Fetch user details for email
$conn = $pdo->open();
try {
    $stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = :user_id");
    $stmt->execute(['user_id' => $id]);
    $user = $stmt->fetch();
    if (!$user) {
        $_SESSION['error'] = 'User not found';
        header('location: ../login.php');
        exit;
    }
    $investor_name = $user['full_name'];
    $investor_email = $user['email'];
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error occurred: ' . $e->getMessage();
    error_log("Database error in user fetch: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
    header('location: ../login.php');
    exit;
}

// Handle new message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        // Check if this is the first message in the chat
        $stmtCheck = $conn->prepare("SELECT COUNT(*) as count FROM live_chat WHERE user_id = ?");
        $stmtCheck->execute([$id]);
        $chatCount = $stmtCheck->fetch(PDO::FETCH_OBJ)->count;

        // Insert message into database
        $stmtInsert = $conn->prepare("INSERT INTO live_chat (user_id, sender, message, date_sent, status) VALUES (:user_id, 'user', :message, NOW(), 0)");
        $stmtInsert->execute(['user_id' => $id, 'message' => $message]);

        // If this is the first message, send email to admin
        if ($chatCount == 0) {
            $sweet_url = isset($sweet_url) ? $sweet_url : 'nexusinsights.it.com';
            $year = date('Y');

            // Email template for admin
            $admin_message = "..."; // (Your existing email template)

            $adminMail = new PHPMailer(true);
            try {
                // Server settings
                $adminMail->isSMTP();
                $adminMail->Host = $smtpConfig['host'];
                $adminMail->SMTPAuth = true;
                $adminMail->Username = $smtpConfig['username'];
                $adminMail->Password = $smtpConfig['password'];
                $adminMail->SMTPSecure = $smtpConfig['secure'];
                $adminMail->Port = $smtpConfig['port'];

                // Recipients
                $adminMail->setFrom($smtpConfig['fromEmail'], $smtpConfig['fromName']);
                $adminMail->addAddress($settings->email2, 'Admin');

                // Content
                $adminMail->isHTML(true);
                $adminMail->Subject = "New Live Chat Initiated - {$settings->siteTitle}";
                $adminMail->Body = $admin_message;

                $adminMail->send();
            } catch (Exception $e) {
                error_log("PHPMailer error in admin notification: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
                $_SESSION['error'] = "Failed to send email notification: {$e->getMessage()}";
            }
        }

        $_SESSION['success'] = "Message sent successfully!";
        header('location: livechat.php');
        exit;
    } else {
        $_SESSION['error'] = "Message cannot be empty.";
    }
}

// Fetch chat messages after all redirects
$stmtQuery = $conn->query("SELECT * FROM live_chat WHERE user_id = $id ORDER BY date_sent ASC");
if ($stmtQuery->rowCount()) {
    $chatMessages = $stmtQuery->fetchAll(PDO::FETCH_OBJ);
}

$pdo->close();

// Include HTML output only after all header() calls
$page_name = 'Live Chat';
$page_parent = '';
$page_title = 'Welcome to the Official Website of ' . $settings->siteTitle;
$page_description = $settings->siteTitle . ' provides quality infrastructure backed high-performance cloud computing services for cryptocurrency mining. Choose a plan to get started today! What are you waiting for? Together We Grow!...';

include('inc/head.php');
?>

<body class="dark-topbar">
    <!-- Left Sidenav -->
    <?php include('inc/sidebar.php'); ?>
    <!-- end left-sidenav-->

    <div class="page-wrapper">
        <!-- Top Bar Start -->
        <?php include('inc/header.php'); ?>
        <!-- Top Bar End -->

        <!-- Page Content-->
        <div class="page-content">
            <!-- Your HTML content -->
            ...
        </div>
    </div>

    <?php include('inc/scripts.php'); ?>
</body>
</html>
