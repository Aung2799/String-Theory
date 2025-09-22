<?php
$host = "localhost";
$user = "root";       // Your MySQL username
$pass = "Koyaykomoe969";           // Your MySQL password
$dbname = "guitar_chords_library"; // Database name

$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character encoding
$conn->set_charset("utf8");

?>
