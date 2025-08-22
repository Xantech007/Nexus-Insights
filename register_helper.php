<?php
include('init.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send activation email
function sendActivationEmail($email, $full_name, $username, $userid, $code, $sweet_url, $settings, $smtpConfig) {
    $message = <<<HTML
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
                                                                                <strong>Dear {$full_name} ({$username}),</strong>
                                                                            </span>
                                                                        </p>
                                                                        <p style='font-size: 13px; line-height: 20px; color: #666666; margin: 0px; text-align: left;' align='center'> </p>
                                                                        <p style='font-size: 13px; line-height: 20px; color: #666666; margin: 0px; text-align: left;' align='center'>
                                                                            <span style='color: #000000;'>
                                                                                Thank you for registering with us. Your new account is being provisioned and can be accessed once activated.
                                                                                <br /><br />
                                                                                <strong>Your account details:</strong><br />
                                                                                Email Address: {$email}<br /><br />
                                                                                Please click the link below to activate your account:
                                                                                <br /><br />
                                                                                <a style='display: inline-block; padding: 10px 20px; background-color: #d60000; color: #ffffff; text-decoration: none; border-radius: 20px;' href='https://{$sweet_url}/activate.php?code={$code}&user={$userid}'>Activate Account</a>
                                                                            </span>
                                                                        </p>
                                                                        <p style='font-size: 13px; line-height: 20px; color: #666666; margin: 0px; text-align: left;' align='center'> </p>
                                                                        <p style='font-size: 13px; line-height: 20px; color: #666666; margin: 0px; text-align: left;' align='center'>
                                                                            <span style='color: #000000;'>
                                                                                Do note that Nexus Insights will not give you any other wallet address apart from the one shown on the website.
                                                                            </span>
                                                                            <br /><br />
                                                                            <span style='color: #000000;'>
                                                                                To report fraudulent activities, contact
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
                                        <table style='font-size: 12px; color: #2d2d2d; line-height: 22px; margin: 0px auto; width: 100%;' border='0' width='100%' cellspacing='0' cellpadding='0' align='center'>
                                            <tbody>
                                                <tr>
                                                    <td lang='en' style='padding: 0px;' align='center'>© {$year} Nexus Insights.</td>
                                                </tr>
                                                <tr>
                                                    <td style='padding: 15px 0px 25px;' align='center'>
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

$conn = $pdo->open();

if (isset($_POST['signup'])) {
    $full_name = $_POST['full_name'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $repassword = $_POST['repassword'] ?? '';
    $referral = empty($_POST['referral']) ? 'nexusinsights' : $_POST['referral'];
    $type = 0;
    $status = 0;

    $_SESSION['full_name'] = $full_name;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;

    if (empty($full_name) || empty($username) || empty($email) || empty($password) || empty($repassword)) {
        $_SESSION['error'] = 'All fields are required';
        header('location: register.php');
        $pdo->close();
        exit;
    }

    if ($password !== $repassword) {
        $_SESSION['error'] = 'Passwords did not match';
        header('location: register.php');
        $pdo->close();
        exit;
    }

    // Check if email is already taken
    $stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM users WHERE email=:email");
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch();

    if ($row['numrows'] > 0) {
        $_SESSION['error'] = 'Email already taken';
        header('location: register.php');
        $pdo->close();
        exit;
    }

    // Check if username is already taken
    $stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM users WHERE uname=:username");
    $stmt->execute(['username' => $username]);
    $row = $stmt->fetch();

    if ($row['numrows'] > 0) {
        $_SESSION['error'] = 'Username already taken';
        header('location: register.php');
        $pdo->close();
        exit;
    }

    $now = date('Y-m-d');
    $password_hashed = password_hash($password, PASSWORD_DEFAULT);

    // Generate activation code
    $set = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = substr(str_shuffle($set), 0, 12);

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
        $email_sent = sendActivationEmail($email, $full_name, $username, $userid, $code, $sweet_url, $settings, $smtpConfig);

        unset($_SESSION['full_name']);
        unset($_SESSION['username']);
        unset($_SESSION['email']);

        $_SESSION['success'] = $email_sent 
            ? 'Account created. Check your email to activate.<br>Didn\'t receive mail? <a href="resend_activation.php">Resend</a>'
            : 'Account created, but email sending failed. <br>Click <a href="resend_activation.php">Resend</a> to try again.';
        header('location: register.php');
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'unique_username') !== false) {
            $_SESSION['error'] = 'Username already taken';
        } else {
            $_SESSION['error'] = 'An error occurred during registration. Please try again.';
        }
        header('location: register.php');
    }
} elseif (isset($_GET['resend']) && isset($_SESSION['resend_data'])) {
    // Handle resend request
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
            : 'Failed to resend activation email. <br>Click <a href="resend_activation.php">Resend</a> to try again.';
    } else {
        $_SESSION['error'] = 'Invalid resend request or account already activated.';
    }
    header('location: register.php');
} else {
    $_SESSION['error'] = 'Fill up signup form first';
    header('location: register.php');
}

$pdo->close();
exit();
?>
