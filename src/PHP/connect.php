<?php
// Database connection details
$servername = "localhost"; // Change this to your database server
$username = "root";        // Change this to your database username
$password = "";            // Change this to your database password
$dbname = "crochet_db"; // Change this to your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Allow from any origin
header("Access-Control-Allow-Origin: *");

// Allow specific methods (GET, POST, OPTIONS)
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Allow headers used in the request
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Allow JSON handling
header('Content-Type: application/json');
