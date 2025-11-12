<?php
$servername = "localhost"; // Correct host
$username = "nexuymmv_db";              // Correct user
$password = "Xander24427279";            // Correct password
$dbname = "nexuymmv_db";        // Correct database name

// Create connection
$conne = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conne->connect_error) {
    header("location:connection_error.php?error=" . $conne->connect_error);
    die($conne->connect_error);
}
?>
