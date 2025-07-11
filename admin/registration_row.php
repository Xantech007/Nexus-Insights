<?php
include 'includes/session.php';

if (isset($_POST['id'])) {
    $conn = $pdo->open();
    
    try {
        $stmt = $conn->prepare("SELECT * FROM registration WHERE id = :id");
        $stmt->execute(['id' => $_POST['id']]);
        $row = $stmt->fetch();
        
        echo json_encode($row);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    
    $pdo->close();
}
?>
