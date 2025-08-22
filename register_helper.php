<?php
include('init.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send activation email
function sendActivationEmail($email, $full_name, $username, $userid, $code, $sweet_url, $settings, $smtpConfig) {
    $message = <<<HTML
    <!-- Keep your existing email template HTML here -->
    <div style='font-family: Helvetica Neue, Helvetica, Roboto, Arial, sans-serif; direction: ltr; background-color: #f3f2f1; margin: 0; padding: 0;'>
        <!-- ... Your existing email template ... -->
    </div>
HTML;

    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = $smtpConfig['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtpConfig['username'];
        $mail->Password = $smtpConfig['password'];
        $mail->SMTPSecure = $smtpConfig['secure'];
        $mail->Port = $smtpConfig['port'];

        // Recipients
        $mail->setFrom($smtpConfig['fromEmail'], $smtpConfig['fromName']);
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "Account Activation";
        $mail->Body = $message;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if (isset($_POST['signup'])) {
    // ... Keep your existing signup code until the email sending part ...

    try {
        $stmt = $conn->prepare("INSERT INTO users (email, password, full_name, uname, referral_code, activate_code, created_on, type, status) VALUES (:email, :password, :full_name, :username, :referral, :code, :now, :type, :status)");
        $stmt->execute([
            'email' => $email,
            'password' => $password_hashed,
            'full_name' => $full_name,
            'username' => $username,
            'referral' => $referral,
            'code' => $code,
            'now' => $now,
            'type' => $type,
            'status' => $status
        ]);
        $userid = $conn->lastInsertId();

        // Store user data in session for potential resend
        $_SESSION['resend_data'] = [
            'email' => $email,
            'full_name' => $full_name,
            'username' => $username,
            'userid' => $userid,
            'code' => $code
        ];

        // Notify Admin
        $msg = "New User Registered: {$email}, Login Admin";
        $msg = wordwrap($msg, 70);
        mail($settings->email2, "New User Alert", $msg);

        // Send initial email
        require 'vendor/autoload.php';
        sendActivationEmail($email, $full_name, $username, $userid, $code, $sweet_url, $settings, $smtpConfig);

        unset($_SESSION['full_name']);
        unset($_SESSION['username']);
        unset($_SESSION['email']);

        $_SESSION['success'] = 'Account created. Check your email to activate.<br>Didn\'t receive mail? <a href="resend_activation.php">Resend</a>';
        header('location: register.php');
        exit();
    } catch (PDOException $e) {
        // ... Keep your existing error handling ...
    } finally {
        $pdo->close();
    }
} elseif (isset($_GET['resend']) && isset($_SESSION['resend_data'])) {
    // Handle resend request
    $conn = $pdo->open();
    
    $resend_data = $_SESSION['resend_data'];
    $email = $resend_data['email'];
    
    // Verify user still exists and is not activated
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=:email AND status=0");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        require 'vendor/autoload.php';
        $success = sendActivationEmail(
            $resend_data['email'],
            $resend_data['full_name'],
            $resend_data['username'],
            $resend_data['userid'],
            $resend_data['code'],
            $sweet_url,
            $settings,
            $smtpConfig
        );

        $_SESSION['success'] = $success 
            ? 'Activation email resent. Check your email to activate.<br>Didn\'t receive mail? <a href="resend_activation.php">Resend</a>'
            : 'Failed to resend activation email. Please try again later.';
    } else {
        $_SESSION['error'] = 'Invalid resend request or account already activated.';
    }
    
    $pdo->close();
    header('location: register.php');
    exit();
} else {
    $_SESSION['error'] = 'Fill up signup form first';
    header('location: register.php');
    exit();
}
?>
