<?php
session_start();
include('init.php');

// Redirect to register_helper.php with resend parameter
header('Location: register_helper.php?resend=1');
exit();
?>
