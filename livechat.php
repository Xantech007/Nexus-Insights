<?php
include('inc/config.php');
include('admin/includes/format.php');
include('inc/session.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php'; // PHPMailer dependency

$page_name = 'Live Chat';
$page_parent = '';
$page_title = 'Welcome to the Official Website of ' . $settings->siteTitle;
$page_description = $settings->siteTitle . ' provides quality infrastructure backed high-performance cloud computing services for cryptocurrency mining. Choose a plan to get started today! What are you waiting for? Together We Grow!...';

include('inc/head.php');

// Initialize variables
$user_id = null;
$guest_id = null;
$investor_name = 'Guest';
$investor_email = 'N/A';

// Open database connection
$conn = $pdo->open();

// Check if user is logged in
if (isset($_SESSION['user'])) {
    $user_id = $_SESSION['user'];
    try {
        $stmt = $conn->prepare("SELECT full_name, email FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $user = $stmt->fetch();
        if ($user) {
            $investor_name = $user['full_name'];
            $investor_email = $user['email'];
        } else {
            // User not found in database, treat as guest
            $user_id = null;
            unset($_SESSION['user']);
        }
    } catch (PDOException $e) {
        error_log("Database error in user fetch: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
        $user_id = null; // Treat as guest if database error occurs
    }
}

// If no user is logged in, assign a guest ID
if (!$user_id) {
    if (!isset($_COOKIE['guest_id'])) {
        $guest_id = bin2hex(random_bytes(16)); // Generate a 32-character unique ID
        setcookie('guest_id', $guest_id, time() + (365 * 24 * 60 * 60), "/", "", true, true); // 1-year cookie, Secure, HttpOnly
    } else {
        $guest_id = $_COOKIE['guest_id'];
    }
}

// Handle new message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = trim($_POST['message']);
    if (!empty($message)) {
        // Check if this is the first message in the chat
        $stmtCheck = $conn->prepare("SELECT COUNT(*) as count FROM live_chat WHERE (user_id = :user_id OR guest_id = :guest_id)");
        $stmtCheck->execute(['user_id' => $user_id ?: 0, 'guest_id' => $guest_id ?: '']);
        $chatCount = $stmtCheck->fetch(PDO::FETCH_OBJ)->count;

        // Insert message into database
        $stmtInsert = $conn->prepare("INSERT INTO live_chat (user_id, guest_id, sender, message, date_sent, status) VALUES (:user_id, :guest_id, 'user', :message, NOW(), 0)");
        $stmtInsert->execute(['user_id' => $user_id, 'guest_id' => $guest_id, 'message' => $message]);

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
                                                                            <strong>Dear Admin,</strong>
                                                                        </span>
                                                                    </p>
                                                                    <p style='font-size: 13px; line-height: 20px; color: #666666; margin: 0px; text-align: left;' align='center'> </p>
                                                                    <p style='font-size: 13px; line-height: 20px; color: #666666; margin: 0px; text-align: left;' align='center'>
                                                                        <span style='color: #000000;'>
                                                                            A new live chat has been initiated with the following details:
                                                                            <br /><br />
                                                                            <strong>User:</strong> {$investor_name}<br />
                                                                            <strong>Email:</strong> {$investor_email}<br />
                                                                            <strong>Guest ID:</strong> {$guest_id ?: 'N/A'}<br />
                                                                            <strong>Message:</strong> {$message}<br /><br />
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

// Fetch chat messages
$stmtQuery = $conn->prepare("SELECT * FROM live_chat WHERE (user_id = :user_id OR guest_id = :guest_id) ORDER BY date_sent ASC");
$stmtQuery->execute(['user_id' => $user_id ?: 0, 'guest_id' => $guest_id ?: '']);
if ($stmtQuery->rowCount()) {
    $chatMessages = $stmtQuery->fetchAll(PDO::FETCH_OBJ);
}

$pdo->close();
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
            <div class="container-fluid">
                <!-- Page-Title -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-title-box">
                            <div class="row">
                                <div class="col">
                                    <h4 class="page-title">Live Chat</h4>
                                </div>
                                <div class="col-auto align-self-center">
                                    <a href="#" class="btn btn-sm btn-outline-primary" id="Dash_Date">
                                        <span class="day-name" id="Day_Name">Today:</span> 
                                        <span class="" id="Select_date"><?php echo date('M d'); ?></span>
                                        <i data-feather="calendar" class="align-self-center icon-xs ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <!-- Display Success/Error Messages -->
                <?php
                if (isset($_SESSION['error'])) {
                    echo "
                        <div class='alert alert-danger border-0' role='alert'>
                            <i class='la la-skull-crossbones alert-icon text-danger align-self-center font-30 mr-3'></i>
                            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                <span aria-hidden='true'><i class='mdi mdi-close align-middle font-16'></i></span>
                            </button>
                            <strong>Oh snap!</strong> " . $_SESSION['error'] . "
                        </div>";
                    unset($_SESSION['error']);
                }
                if (isset($_SESSION['success'])) {
                    echo "
                        <div class='alert alert-success border-0' role='alert'>
                            <i class='mdi mdi-check-all alert-icon'></i>
                            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                <span aria-hidden='true'><i class='mdi mdi-close align-middle font-16'></i></span>
                            </button>
                            <strong>Well done!</strong> " . $_SESSION['success'] . "
                        </div>";
                    unset($_SESSION['success']);
                }
                ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Chat Messages -->
                                <div class="chat-box" style="max-height: 400px; overflow-y: auto;">
                                    <?php if (!empty($chatMessages)) : ?>
                                        <?php foreach ($chatMessages as $msg) : ?>
                                            <div class="chat-message mb-3 <?php echo $msg->sender === 'user' ? 'text-right' : 'text-left'; ?>">
                                                <div class="card p-2 d-inline-block <?php echo $msg->sender === 'user' ? 'bg-light' : 'bg-primary text-white'; ?>">
                                                    <p class="mb-1"><?php echo htmlspecialchars($msg->message); ?></p>
                                                    <small class="text-muted"><?php echo $msg->date_sent; ?> - <?php echo $msg->sender === 'user' ? ($user_id ? 'You' : 'Guest') : 'Admin'; ?></small>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <p>No messages yet. Start a conversation below!</p>
                                    <?php endif; ?>
                                </div>

                                <!-- Message Input Form -->
                                <form method="POST" action="">
                                    <div class="input-group mt-3">
                                        <textarea name="message" class="form-control" rows="3" placeholder="Type your message..." required></textarea>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">Send</button>
                                        </div>
                                    </div>
                                </form>
                            </div><!--end card-body-->
                        </div><!--end card-->
                    </div><!--end col-->
                </div><!--end row-->
            </div><!-- container -->

            <?php include('inc/footer.php'); ?><!--end footer-->
        </div>
        <!-- end page content -->
    </div>
    <!-- end page-wrapper -->

    <?php include('inc/scripts.php'); ?>
</body>
</html>
