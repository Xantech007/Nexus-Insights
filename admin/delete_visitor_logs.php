<?php
// delete_visitor_logs.php
session_start();
include '../account/connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['visitor_id'])) {
    $visitor_id = $_POST['visitor_id'];

    try {
        $stmt = $conne->prepare("DELETE FROM visitor_logs WHERE visitor_id = ?");
        $stmt->bind_param("s", $visitor_id);
        $stmt->execute();
        $_SESSION['success'] = 'Visitor logs deleted successfully';
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error deleting logs: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Invalid request';
}

header('Location: tracking_logs.php');
exit;
?>
