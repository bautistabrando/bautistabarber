<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "barber_booking";

try {
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

function sanitizeInput($data) {
    global $conn;
    return htmlspecialchars(trim($conn->real_escape_string($data)));
}
?>