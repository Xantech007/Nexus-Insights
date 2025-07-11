<?php
include 'includes/session.php';

if (isset($_POST['activate'])) {
    $id = $_POST['id'];
    
    $conn = $pdo->open();

    try {
        $now = date('Y-m-d g:i A');

        // Fetch the amount and description from the registration table where id = 1
        $stmt = $conn->prepare("SELECT amount, description FROM registration WHERE id = :id");
        $stmt->execute(['id' => 1]);
        $registration = $stmt->fetch();

        if ($registration) {
            $bonus_amount = $registration['amount'];
            $bonus_description = $registration['description'];

            // Insert transaction with dynamic amount and description
            $stmt1 = $conn->prepare("INSERT INTO transaction (id, user_id, date, type, amount, description, balance) VALUES (NULL, :user_id, :date, :type, :amount, :description, :balance)");
            $stmt1->execute([
                'user_id' => $id,
                'date' => $now,
                'type' => 1,
                'amount' => $bonus_amount,
                'description' => $bonus_description,
                'balance' => $bonus_amount
            ]);

            // Insert into activity table
            $stmt2 = $conn->prepare("INSERT INTO activity (act_id, user_id, message, category, date_sent) VALUES (NULL, :user_id, :message, :category, :date_sent)");
            $stmt2->execute([
                'user_id' => $id,
                'message' => "Received Registration Bonus of $bonus_amount",
                'category' => 'Deposit',
                'date_sent' => $now
            ]);

            // Update user status
            $stmt3 = $conn->prepare("UPDATE users SET status = :status WHERE id = :id");
            $stmt3->execute(['status' => 1, 'id' => $id]);

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
