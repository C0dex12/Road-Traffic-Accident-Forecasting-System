<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "thesis_2025";

// Create database connection
$conn = new mysqli($servername, $username, $password, $database);

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Example query (ensure it is defined before using prepare)
$check_query = "SELECT * FROM users WHERE email = ? OR username_or_badge = ?";

// Prepare the statement
if (!$stmt = $conn->prepare($check_query)) {
    die("Prepare failed: " . $conn->error);
}

// echo "Database connection successful, and SQL statement prepared successfully.";
?>
