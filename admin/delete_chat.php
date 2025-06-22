<?php
include 'includes/session.php';
include '../inc/config.php';

if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = 'Please log in to access the admin panel';
    header('location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $id = $_POST['id'];
    $type = $_POST['type'];

    try {
        $conn = $pdo->open();

        if ($type === 'user') {
            $stmt = $conn->prepare("DELETE FROM live_chat WHERE user_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        } else {
            $stmt = $conn->prepare("DELETE FROM live_chat WHERE guest_id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_STR);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = 'Chat history deleted successfully!';
        } else {
            $_SESSION['error'] = 'Failed to delete chat history.';
        }

        $pdo->close();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error occurred: ' . $e->getMessage();
        error_log("Database error in delete_chat: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
    }
}

header('location: livechat.php');
exit;
?>
