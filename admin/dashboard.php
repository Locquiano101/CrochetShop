<?php
// Database connection details
$servername = "srv1632.hstgr.io"; // Change this to your database server
$username = "u143688490_greg";        // Change this to your database username
$password = "Fujiwara000";            // Change this to your database password
$dbname = "u143688490_crochetshop2"; // Change this to your database name

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


// Fetch all products from the database
$sql = "SELECT * FROM products";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $products = [];

    // Fetch all products and store them in an array
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }

    // Return the products array as JSON
    echo json_encode($products);
} else {
    echo json_encode(['error' => 'No products found']);
}

$conn->close();
