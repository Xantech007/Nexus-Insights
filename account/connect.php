<?php
$servername = "localhost";
// Enter your MySQL username below(default=root)
$username = "nexuymmv_db";
// Enter your MySQL password below
$password = "Xander24427279";
$dbname = "nexuymmv_db";

// Create connection
$conne = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conne->connect_error) {
    header("location:connection_error.php?error=$conne->connect_error");
    die($conne->connect_error);
}
?>
