<?php
include 'includes/session.php';

if (isset($_POST['activate'])) {
    $id = $_POST['id'];
    
    $conn = $pdo->open();

    try {
        $now = date('Y-m-d g:i A');

        // Fetch the amount and description from the registration table where id = 1
        $sql = "SELECT amount, description FROM registration WHERE id = 1";
        $result = $conn->query($sql);
        $registration = $result->fetch();

        if ($registration) {
            $bonus_amount = $registration['amount'];
            $bonus_description = $registration['description'];

            // Insert transaction with dynamic amount and description
            $sql1 = "INSERT INTO transaction VALUES(
                        NULL,
                        '$id',
                        '$now',
                        '1',
                        '$bonus_amount',
                        '$bonus_description',
                        '$bonus_amount'
                    )";
            $conn->query($sql1);

            // Insert into activity table
            $sql2 = "INSERT INTO activity (act_id, user_id, message, category, date_sent) VALUES (
                        NULL,
                        '$id',
                        '$bonus_description ($$bonus_amount)',
                        'Info',
                        '$now'
                    )";
            $conn->query($sql2);

            // Update user status
            $stmt = $conn->prepare("UPDATE users SET status=:status WHERE id=:id");
            $stmt->execute(['status' => 1, 'id' => $id]);
            $_SESSION['success'] = 'User activated successfully';
        } else {
            $_SESSION['error'] = 'Registration bonus details not found';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = $e->getMessage();
    }

    $pdo->close();
} else {
    $_SESSION['error'] = 'Select user to activate first';
}

header('location: users.php');
?>
