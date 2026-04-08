<?php
$host = "localhost";
$db_name = "county_complaints";
$username = "root"; // Default WAMP username
$password = "";     // Default WAMP password is empty

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    // Set the PDO error mode to exception for professional error handling
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $exception) {
    echo "Connection error: " . $exception->getMessage();
}
?>