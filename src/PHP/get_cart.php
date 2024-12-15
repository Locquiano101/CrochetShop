<?php

// Database connection
include_once("connect.php");

// Fetch all products from the database
$sql = "SELECT 
    p.prod_id,
    p.prod_img_file,
    p.prod_name,
    p.prod_description,
    oi.order_item_id,
    oi.quantity,
    oi.total_price,
    oi.order_status

    FROM 
        orders AS o 
    INNER JOIN 
        order_items AS oi ON o.order_id = oi.order_id 
    INNER JOIN 
        products AS p ON oi.prod_id = p.prod_id";


$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $cart_item = [];

    // Fetch all products and store them in an array
    while ($row = $result->fetch_assoc()) {
        $cart_item[] = $row;
    }

    // Return the products array as JSON
    echo json_encode($cart_item);
} else {
    echo json_encode(['error' => 'No products found']);
}

$conn->close();
