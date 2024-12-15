
<?php

// Database connection
include_once("connect.php");

// Fetch all products from the database
$sql = "SELECT 
    r.rev_id, 
    r.rev_rate, 
    r.rev_comment, 
    r.rev_date, 
    u.user_firstName, 
    u.user_lastName, 
    p.prod_name
FROM 
    reviews r
INNER JOIN 
    Customers u ON r.customer_id = u.user_id
INNER JOIN 
    order_items oi ON r.product_id = oi.prod_id
INNER JOIN 
    orders o ON oi.order_id = o.order_id
INNER JOIN 
    products p ON oi.prod_id = p.prod_id;
";

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
