<?php
include 'includes/session.php';

if (isset($_POST['save_bonus'])) {
    $conn = $pdo->open();
    
    try {
        $id = $_POST['id'];
        $amount = $_POST['amount'];
        $description = $_POST['description'];

        // Check if record with id=1 exists
        $stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM registration WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row['numrows'] > 0) {
            // Update existing record
            $stmt = $conn->prepare("UPDATE registration SET amount = :amount, description = :description WHERE id = :id");
            $stmt->execute(['amount' => $amount, 'description' => $description, 'id' => $id]);
            $_SESSION['success'] = 'Registration bonus updated successfully';
        } else {
            // Insert new record
            $stmt = $conn->prepare("INSERT INTO registration (id, amount, description) VALUES (:id, :amount, :description)");
            $stmt->execute(['id' => $id, 'amount' => $amount, 'description' => $description]);
            $_SESSION['success'] = 'Registration bonus created successfully';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    $pdo->close();
} else {
    $_SESSION['error'] = 'Fill up the bonus form first';
}

header('location: users.php');
?>
