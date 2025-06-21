<?php
// Enable error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start output buffering to capture any unintended output
ob_start();

include('init.php');
include('admin/includes/format.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // PHPMailer dependency

// Check if user is logged in
if (isset($_SESSION['user'])) {
    header('Location: account/livechat.php');
    exit;
}

// Initialize variables for guest
$guest_id = null;
$investor_name = 'Guest';
$investor_email = 'N/A';

// Open database connection
$conn = $pdo->open();

// Assign or validate guest ID
if (!isset($_COOKIE['guest_id']) || empty($_COOKIE['guest_id']) || !preg_match('/^[a-f0-9]{32}$/', $_COOKIE['guest_id'])) {
    $guest_id = bin2hex(random_bytes(16)); // 32-character unique ID
    setcookie('guest_id', $guest_id, time() + (365 * 24 * 60 * 60), "/", "", true, true); // 1-year cookie, Secure, HttpOnly
} else {
    $guest_id = $_COOKIE['guest_id'];
}

// Handle new message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        // Check if this is the first message in the chat
        $stmtCheck = $conn->prepare("SELECT COUNT(*) as count FROM live_chat WHERE guest_id = :guest_id");
        $stmtCheck->bindParam(':guest_id', $guest_id, PDO::PARAM_STR);
        $stmtCheck->execute();
        $chatCount = $stmtCheck->fetch(PDO::FETCH_ASSOC)['count'];

        // Insert message into database
        $stmtInsert = $conn->prepare("INSERT INTO live_chat (user_id, guest_id, sender, message, date_sent, status) VALUES (:user_id, :guest_id, 'user', :message, NOW(), 0)");
        $user_id = 0; // Always 0 for guests
        $stmtInsert->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmtInsert->bindParam(':guest_id', $guest_id, PDO::PARAM_STR);
        $stmtInsert->bindParam(':message', $message, PDO::PARAM_STR);
        $stmtInsert->execute();

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
                                                    <a style='text-decoration: underline; color: #cca354;' href='https://{$sweet_url}/investment'> View Our Available Plans </a>
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
                                                    <span><a style='text-decoration: underline; color: #cca354;' href='https://{$sweet_url}'>Home</a>|</span>
                                                    <a style='text-decoration: underline; color: #cca354;' href='https://{$sweet_url}/about'>About</a>
                                                    <span>|</span>
                                                    <a style='text-decoration: underline; color: #cca354;' href='https://{$sweet_url}/investment'>Plans</a>
                                                    <br />
                                                    <a style='text-decoration: underline; color: #cca354;' href='https://{$sweet_url}/news'>News</a>
                                                    <span>|</span>
                                                    <a style='text-decoration: underline; color: #cca354;' href='https://{$sweet_url}/contact'>Contact</a>
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
                $adminMail->setFrom($smtpConfig['sender_email'], $smtpConfig['sender_name']);
                $adminMail->addAddress($settings->email2); // Admin email

                // Content
                $adminMail->isHTML(true);
                $adminMail->Subject = 'New Live Chat Initiated';
                $adminMail->Body = $admin_message;

                $adminMail->send();
            } catch (Exception $e) {
                // Log error (in production, use proper logging)
                error_log("Email could not be sent. Mailer Error: {$adminMail->ErrorInfo}");
            }
        }

        // Redirect to refresh messages
        header('Location: livechat.php');
        exit;
    }
}

// Fetch chat messages
$stmtMessages = $conn->prepare("SELECT * FROM live_chat WHERE guest_id = :guest_id ORDER BY date_sent ASC");
$stmtMessages->bindParam(':guest_id', $guest_id, PDO::PARAM_STR);
$stmtMessages->execute();
$messages = $stmtMessages->fetchAll(PDO::FETCH_OBJ);

// Close database connection
$pdo->close();

$page_name = 'Live Chat';
$page_parent = '';
$page_title = 'Welcome to the Official Website of ' . $settings->siteTitle;
$page_description = $settings->siteTitle . ' provides quality infrastructure backed high-performance cloud computing services for cryptocurrency mining. Choose a plan to get started today! What are you waiting for? Together We Grow!...';
include('inc/head.php');
?>

<style>
/* Custom styles for chat input and button to match home icon (#cca354) */
.chat-box .form-control {
    border: 1px solid #cca354;
    background-color: transparent;
    color: #ffffff;
}
.chat-box .form-control:focus {
    border-color: #cca354;
    box-shadow: 0 2px 5px rgba(204, 163, 84, 0.5);
    background-color: transparent;
    color: #ffffff;
}
.chat-box .form-control::-webkit-input-placeholder {
    color: rgba(255, 255, 255, 0.7);
}
.chat-box .form-control::-moz-placeholder {
    color: rgba(255, 255, 255, 0.7);
}
.chat-box .form-control:-ms-input-placeholder {
    color: rgba(255, 255, 255, 0.7);
}
.chat-box .form-control:-moz-placeholder {
    color: rgba(255, 255, 255, 0.7);
}
.chat-box .btn-primary {
    background-color: #cca354;
    border-color: #cca354;
    color: #000000;
    padding: 10px 20px;
    border-radius: 3px;
}
.chat-box .btn-primary:hover,
.chat-box .btn-primary:focus {
    background-color: #b78b36;
    border-color: #b78b36;
    color: #000000;
}
</style>

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
                            <li><a href="<?= $baseurl; ?>">Home</a></li>
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
                    <div class="col-lg-8">
                        <div class="chat-box">
                            <div class="chat-header">
                                <h3>Chat with Support</h3>
                                <p>Our support team is here to assist you. Ask any questions!</p>
                            </div>
                            <div class="chat-body">
                                <?php foreach ($messages as $msg): ?>
                                    <div class="message <?= $msg->sender === 'user' ? 'sent' : 'received' ?>">
                                        <p><strong><?= $msg->sender === 'user' ? 'You' : 'Support' ?>:</strong> <?= htmlspecialchars($msg->message) ?></p>
                                        <small><?= date('M d, Y H:i', strtotime($msg->date_sent)) ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <form method="POST" action="">
                                <div class="input-group mt-3">
                                    <textarea name="message" class="form-control" rows="3" placeholder="Type your message..." required></textarea>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-primary">Send</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- chat section end -->

        <!-- footer section start -->
        <?php include('inc/footer.php') ?>
        <!-- footer section end -->
    </div> <!-- page-wrapper end -->

    <?php include('inc/scripts.php') ?>
</body>
</html>
<?php
// End output buffering
ob_end_flush();
?>
