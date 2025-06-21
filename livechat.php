<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Start output buffering
ob_start();

include('init.php');
include('admin/includes/format.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // PHPMailer dependency

// Log script start
error_log("livechat.php loaded at " . date('Y-m-d H:i:s'), 3, __DIR__ . "/debug_log.txt");

// Check if user is logged in
if (isset($_SESSION['user'])) {
    error_log("User logged in, redirecting to account/livechat.php", 3, __DIR__ . "/debug_log.txt");
    header('Location: account/livechat.php');
    exit;
}

// Generate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Initialize variables for guest
$guest_id = null;
$investor_name = 'Guest';
$investor_email = 'N/A';

// Open database connection
try {
    $conn = $pdo->open();
    error_log("Database connection opened", 3, __DIR__ . "/debug_log.txt");
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
    die("Database connection failed. Please try again later.");
}

// Assign or validate guest ID
if (!isset($_COOKIE['guest_id']) || empty($_COOKIE['guest_id']) || !preg_match('/^[a-f0-9]{32}$/', $_COOKIE['guest_id'])) {
    $guest_id = bin2hex(random_bytes(16)); // 32-character unique ID
    setcookie('guest_id', $guest_id, time() + (30 * 24 * 60 * 60), "/", "", true, true); // 30-day cookie, Secure, HttpOnly
    error_log("New Guest ID generated: $guest_id", 3, __DIR__ . "/debug_log.txt");
} else {
    $guest_id = $_COOKIE['guest_id'];
    // Refresh cookie to ensure persistence
    setcookie('guest_id', $guest_id, time() + (30 * 24 * 60 * 60), "/", "", true, true);
    error_log("Existing Guest ID used: $guest_id, Cookie refreshed", 3, __DIR__ . "/debug_log.txt");
}

// Handle new message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'], $_POST['csrf_token'])) {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        error_log("Invalid CSRF token for Guest ID $guest_id", 3, __DIR__ . "/error_log.txt");
        $_SESSION['error'] = "Invalid form submission.";
        header('Location: livechat.php');
        exit;
    }

    $message = trim($_POST['message']);
    error_log("POST request received, Guest ID: $guest_id, Message: $message", 3, __DIR__ . "/debug_log.txt");

    if (!empty($message)) {
        try {
            // Check if this is the first message in the chat
            $stmtCheck = $conn->prepare("SELECT COUNT(*) as count FROM guest_live_chat WHERE guest_id = :guest_id");
            $stmtCheck->bindParam(':guest_id', $guest_id, PDO::PARAM_STR);
            $stmtCheck->execute();
            $chatCount = $stmtCheck->fetch(PDO::FETCH_ASSOC)['count'];
            error_log("Chat count for Guest ID $guest_id: $chatCount", 3, __DIR__ . "/debug_log.txt");

            // Insert message into database
            $stmtInsert = $conn->prepare("INSERT INTO guest_live_chat (guest_id, sender, message, date_sent, status) VALUES (:guest_id, 'user', :message, NOW(), 0)");
            $stmtInsert->bindParam(':guest_id', $guest_id, PDO::PARAM_STR);
            $stmtInsert->bindParam(':message', $message, PDO::PARAM_STR);
            $stmtInsert->execute();
            error_log("Message inserted for Guest ID $guest_id: $message", 3, __DIR__ . "/debug_log.txt");

            // Verify insertion
            $stmtVerify = $conn->prepare("SELECT id FROM guest_live_chat WHERE guest_id = :guest_id AND message = :message ORDER BY date_sent DESC LIMIT 1");
            $stmtVerify->bindParam(':guest_id', $guest_id, PDO::PARAM_STR);
            $stmtVerify->bindParam(':message', $message, PDO::PARAM_STR);
            $stmtVerify->execute();
            $insertedId = $stmtVerify->fetchColumn();
            error_log("Verified insertion for Guest ID $guest_id, Message ID: $insertedId", 3, __DIR__ . "/debug_log.txt");

            // If this is the first message, send email to admin
            if ($chatCount == 0) {
                $sweet_url = isset($sweet_url) ? $sweet_url : 'nexusinsights.it.com'; // Fallback URL
                $year = date('Y');

                // Email template for admin
                $admin_message = <<<HTML
<div style='font-family: Helvetica Neue, Helvetica, Roboto, Arial, sans-serif; direction: ltr; background-color: #f3f2f1; margin: 0; padding: 0;'>
    <table class='main' border='0' width='100%' cellspacing='0' cellpadding='0' bgcolor='#F3F2F1'>
        <tbody>
            <tr>
                <td class='outer-box' style='padding: 0 8px;' align='center' bgcolor='#F3F2F1'>
                    <table style='max-width: 600px; padding: 0 0 15px 0;' border='0' width='100%' cellspacing='0' cellpadding='0'>
                        <tbody>
                            <tr>
                                <td style='padding: 10px 0 13px 0;' align='left'>
                                    <a href='https://{$sweet_url}'>
                                        <img
                                            style='display: block;'
                                            src='https://{$sweet_url}/assets/images/logo-dark.png'
                                            alt='nexus-logo'
                                            width='300'
                                            height='60'
                                            border='0'
                                        />
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <table class='width-600' style='max-width: 600px;' border='0' width='100%' cellspacing='0' cellpadding='0' bgcolor='#FFFFFF'>
                        <tbody>
                            <tr>
                                <td class='content-box' style='padding-bottom: 24px !important;'>
                                    <table border='0' width='100%' cellspacing='0' cellpadding='0'>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <table border='0' width='100%' cellspacing='0' cellpadding='0'>
                                                        <tbody>
                                                            <tr>
                                                                <td style='padding: 16px 10px 0;'>
                                                                    <p style='font-size: 13px; line-height: 20px; color: #666666; margin: 0px; text-align: left;' align='center'>
                                                                        <span style='font-size: 12pt; font-family: arial black, sans-serif; color: #000000;'>
                                                                            <strong>Dear Livechat Agent,</strong>
                                                                        </span>
                                                                    </p>
                                                                    <p style='font-size: 13px; line-height: 20px; color: #666666; margin: 0px; text-align: left;' align='center'> </p>
                                                                    <p style='font-size: 13px; line-height: 20px; color: #666666; margin: 0px; text-align: left;' align='center'>
                                                                        <span style='color: #000000;'>
                                                                            A new live chat has been initiated with the following details:
                                                                            <br /><br />
                                                                            <strong>User:</strong> {$investor_name}<br />
                                                                            <strong>Email:</strong> {$investor_email}<br />
                                                                            <strong>Guest ID:</strong> {$guest_id}<br />
                                                                            <strong>Message:</strong> {$message}<br /><br />
                                                                            <strong>Admin Panel:</strong> <a href='https://{$sweet_url}/admin'>Login to respond</a><br /><br />
                                                                            Please log in to the admin panel to respond to this chat.
                                                                        </span>
                                                                    </p>
                                                                    <p style='font-size: 13px; line-height: 20px; color: #666666; margin: 0px; text-align: left;' align='center'> </p>
                                                                    <p style='font-size: 13px; line-height: 20px; color: #666666; margin: 0px; text-align: left;' align='center'>
                                                                        <span style='color: #000000;'>
                                                                            For any issues, contact
                                                                            <strong><a style='color: #000000;' href='mailto:{$settings->email2}'>support@nexusinsights.it.com</a></strong>
                                                                        </span>
                                                                    </p>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td> </td>
                            </tr>
                        </tbody>
                    </table>
                    <table style='max-width: 550px; width: 100%;' border='0' cellspacing='0' cellpadding='0' bgcolor='#F2F2F2'>
                        <tbody>
                            <tr>
                                <td style='padding: 24px 4px; width: 100%;'>
                                    <table style='max-width: 424px;' border='0' cellspacing='0' cellpadding='0' align='center'>
                                        <tbody>
                                            <tr>
                                                <td style='font-size: 12px; line-height: 16px; color: #4b4b4b; padding: 20px 0; margin: 0 auto;' align='center'>
                                                    *This email account is not monitored. Reply to <a href='mailto:{$settings->email2}'>{$settings->email2}</a> if you have any query.
                                                    <a style='text-decoration: underline; color: #085ff7;' href='https://{$sweet_url}/investment'> View Our Available Plans </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table style='font-size: 12px; color: #2d2d2d; line-height: 22px; margin: 0px auto; width: 100%;' border='0' width='100%' cellspacing='0' cellpadding='0' align='middle'>
                                        <tbody>
                                            <tr>
                                                <td lang='en' style='padding: 0px;' align='middle'>© {$year} Nexus Insights.</td>
                                            </tr>
                                            <tr>
                                                <td style='padding: 15px 0px 25px;' align='middle'>
                                                    <span><a style='text-decoration: underline; color: #085ff7;' href='https://{$sweet_url}'>Home</a>|</span>
                                                    <a style='text-decoration: underline; color: #085ff7;' href='https://{$sweet_url}/about'>About</a>
                                                    <span>|</span>
                                                    <a style='text-decoration: underline; color: #085ff7;' href='https://{$sweet_url}/investment'>Plans</a>
                                                    <br />
                                                    <a style='text-decoration: underline; color: #085ff7;' href='https://{$sweet_url}/news'>News</a>
                                                    <span>|</span>
                                                    <a style='text-decoration: underline; color: #085ff7;' href='https://{$sweet_url}/contact'>Contact</a>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>
HTML;

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
                    $adminMail->addAddress($settings->email2, 'Livechat Agent');

                    // Content
                    $adminMail->isHTML(true);
                    $adminMail->Subject = "New Live Chat Initiated - {$settings->siteTitle}";
                    $adminMail->Body = $admin_message;

                    $adminMail->send();
                    error_log("Email notification sent for Guest ID $guest_id", 3, __DIR__ . "/debug_log.txt");
                } catch (Exception $e) {
                    error_log("PHPMailer error for Guest ID $guest_id: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
                    $_SESSION['error'] = "Message sent, but failed to notify admin.";
                }
            }

            $_SESSION['success'] = "Message sent successfully!";
            error_log("Success message set for Guest ID $guest_id, redirecting", 3, __DIR__ . "/debug_log.txt");
            header('Location: livechat.php');
            exit;
        } catch (PDOException $e) {
            error_log("Database error for Guest ID $guest_id: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
            $_SESSION['error'] = "Failed to send message due to a database error.";
            header('Location: livechat.php');
            exit;
        }
    } else {
        $_SESSION['error'] = "Message cannot be empty.";
        error_log("Empty message submitted for Guest ID $guest_id", 3, __DIR__ . "/debug_log.txt");
    }
}

// Fetch chat messages
try {
    $stmtQuery = $conn->prepare("SELECT id, guest_id, sender, message, date_sent, status FROM guest_live_chat WHERE guest_id = :guest_id ORDER BY date_sent ASC");
    $stmtQuery->bindParam(':guest_id', $guest_id, PDO::PARAM_STR);
    $stmtQuery->execute();
    $chatMessages = $stmtQuery->fetchAll(PDO::FETCH_ASSOC);
    error_log("Guest ID: $guest_id, Messages retrieved: " . count($chatMessages), 3, __DIR__ . "/debug_log.txt");

    // Log message details
    foreach ($chatMessages as $msg) {
        error_log("Message ID: {$msg['id']}, Sender: {$msg['sender']}, Message: {$msg['message']}, Date: {$msg['date_sent']}", 3, __DIR__ . "/debug_log.txt");
    }
} catch (PDOException $e) {
    error_log("Query error for Guest ID $guest_id: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
    $chatMessages = [];
    $_SESSION['error'] = "Failed to load messages due to a database error.";
}

$pdo->close();
error_log("Database connection closed", 3, __DIR__ . "/debug_log.txt");

// Page metadata for head.php
$page_name = 'Live Chat';
$page_parent = '';
$page_title = 'Welcome to the Official Website of ' . $settings->siteTitle;
$page_description = $settings->siteTitle . ' provides quality infrastructure backed high-performance cloud computing services for cryptocurrency mining. Choose a plan to get started today! What are you waiting for? Together We Grow!...';

// Now include head.php and output HTML
include('inc/head.php');
?>

<body>
    <!-- scroll-to-top start -->
    <?php include('inc/scroll-to-top.php'); ?>  
    <!-- scroll-to-top end -->

    <!-- STAR ANIMATION -->
    <?php include('inc/star-animation.php'); ?>
    <!-- / STAR ANIMATION -->

    <div class="page-wrapper">
        <!-- header-section start -->
        <?php include('inc/header.php'); ?>    
        <!-- header-section end -->

        <!-- inner hero start -->
        <section class="inner-hero bg_img" data-background="assets/images/bg/bg-1.jpg">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <h2 class="page-title">Live Chat</h2>
                        <ul class="page-breadcrumb">
                            <li><a href="<?= $baseurl ?>">Home</a></li>
                            <li>Live Chat</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <!-- inner hero end -->

        <!-- chat section start -->
        <section class="pt-120 pb-120">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <!-- Display Success/Error Messages -->
                        <?php if (isset($_SESSION['error'])) : ?>
                            <div class='alert alert-danger border-0' role='alert'>
                                <i class='la la-skull-crossbones alert-icon text-danger align-self-center font-30 mr-3'></i>
                                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                    <span aria-hidden='true'><i class='mdi mdi-close align-middle font-16'></i></span>
                                </button>
                                <strong>Oh snap!</strong> <?= htmlspecialchars($_SESSION['error']) ?>
                            </div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['success'])) : ?>
                            <div class='alert alert-success border-0' role='alert'>
                                <i class='mdi mdi-check-all alert-icon'></i>
                                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                    <span aria-hidden='true'><i class='mdi mdi-close align-middle font-16'></i></span>
                                </button>
                                <strong>Well done!</strong> <?= htmlspecialchars($_SESSION['success']) ?>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <div class="card">
                            <div class="card-body">
                                <!-- Chat Messages -->
                                <div class="chat-box" id="chat-box" style="max-height: 400px; overflow-y: auto;">
                                    <?php if (!empty($chatMessages)) : ?>
                                        <?php foreach ($chatMessages as $msg) : ?>
                                            <div class="chat-message mb-3 <?= $msg['sender'] === 'user' ? 'text-right' : 'text-left'; ?>">
                                                <div class="card p-2 d-inline-block <?= $msg['sender'] === 'user' ? 'bg-light text-black' : 'bg-primary text-black'; ?>">
                                                    <p class="mb-1"><?= htmlspecialchars($msg['message']) ?></p>
                                                    <small class="text-muted"><?= htmlspecialchars($msg['date_sent']) ?> - <?= $msg['sender'] === 'user' ? 'Guest' : 'Livechat Agent'; ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <p>No messages yet. Start a conversation below!</p>
                                    <?php endif; ?>
                                </div>

                                <!-- Message Input Form -->
                                <form method="POST" action="">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                    <div class="input-group mt-3">
                                        <textarea name="message" class="form-control" rows="3" placeholder="Type your message..." required></textarea>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">Send</button>
                                        </div>
                                    </div>
                                </form>
                            </div><!-- end card-body -->
                        </div><!-- end card -->
                    </div><!-- end col -->
                </div><!-- end row -->
            </div><!-- end container -->
        </section>
        <!-- chat section end -->

        <!-- footer section start -->
        <?php include('inc/footer.php'); ?>
        <!-- footer section end -->
    </div><!-- page-wrapper end -->

    <?php include('inc/scripts.php'); ?>
    <!-- Auto-scroll to bottom of chat box -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var chatBox = document.getElementById('chat-box');
            chatBox.scrollTop = chatBox.scrollHeight;
        });
    </script>
</body>
</html>

<?php
// Flush output buffer
ob_end_flush();
?>
