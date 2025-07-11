<?php
include 'includes/session.php';

if(isset($_POST['id'])){
    $id = $_POST['id'];
    $conn = $pdo->open();

    try{
        $stmt = $conn->prepare("SELECT * FROM limits WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        echo json_encode($row);
    }
    catch(PDOException $e){
        echo json_encode(['error' => $e->getMessage()]);
    }

    $pdo->close();
}
?>
