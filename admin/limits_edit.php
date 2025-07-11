<?php
include 'includes/session.php';

if(isset($_POST['edit'])){
    $id = $_POST['id'];
    $min_deposit = $_POST['min_deposit'];
    $max_deposit = $_POST['max_deposit'];
    $min_withdraw = $_POST['min_withdraw'];
    $max_withdraw = $_POST['max_withdraw'];

    $conn = $pdo->open();

    try{
        $stmt = $conn->prepare("UPDATE limits SET min_deposit=:min_deposit, max_deposit=:max_deposit, min_withdraw=:min_withdraw, max_withdraw=:max_withdraw WHERE id=:id");
        $stmt->execute([
            'min_deposit' => $min_deposit,
            'max_deposit' => $max_deposit,
            'min_withdraw' => $min_withdraw,
            'max_withdraw' => $max_withdraw,
            'id' => $id
        ]);
        $_SESSION['success'] = 'Limits updated successfully';
    }
    catch(PDOException $e){
        $_SESSION['error'] = $e->getMessage();
    }

    $pdo->close();
}
else{
    $_SESSION['error'] = 'Fill up edit limits form first';
}

header('location: investment_plans.php');
?>
