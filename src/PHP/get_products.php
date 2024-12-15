<?php

// Database connection
include_once("connect.php");

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
