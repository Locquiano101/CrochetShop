<?php
// Database connection
include_once("connect.php");

// Set header to return JSON
header('Content-Type: application/json');

try {
    // Check if the connection is valid
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // SQL query to join tables and fetch data
    $sql = "
            SELECT 
            o.order_id,
            o.customer_id,
            o.order_date,
            o.total_amount,
            o.shipping_Address,
            c.user_firstName,
            c.user_lastName,
            oi.order_item_id,
            oi.quantity,
            oi.total_price,
            oi.order_status,
            p.prod_id,
            p.prod_name,
            p.prod_description
        FROM 
            orders AS o
        INNER JOIN 
            customers AS c ON o.customer_id = c.user_id
        INNER JOIN 
            order_items AS oi ON o.order_id = oi.order_id
        INNER JOIN 
            products AS p ON oi.prod_id = p.prod_id;
    ";

    // Execute the query
    $result = $conn->query($sql);

    if ($result === false) {
        throw new Exception("Query failed: " . $conn->error);
    }

    // Fetch results into an array
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }

    // Check if data was found
    if (empty($orders)) {
        echo json_encode(['error' => 'No orders found.']);
    } else {
        echo json_encode($orders);
    }
} catch (Exception $e) {
    // Return an error message
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    // Close the database connection
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
