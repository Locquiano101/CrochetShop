<?php

// Database connection
include_once("connect.php");

// Set the header to return JSON
header('Content-Type: application/json');

try {
    // Check if the connection object exists and is valid
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // SQL query to fetch reviews and related details
    $sql = "
        SELECT 
            r.rev_id, 
            r.rev_rate, 
            r.rev_comment, 
            r.rev_date, 
            u.user_firstName, 
            u.user_lastName, 
            p.prod_name
        FROM 
            reviews r
        LEFT JOIN 
            customers u ON r.customer_id = u.user_id
        LEFT JOIN 
            order_items oi ON r.product_id = oi.prod_id
        LEFT JOIN 
            orders o ON oi.order_id = o.order_id
        LEFT JOIN 
            products p ON oi.prod_id = p.prod_id
    ";

    $result = $conn->query($sql);

    if ($result === false) {
        throw new Exception("Query execution failed: " . $conn->error);
    }

    // Fetch all rows
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }

    // Debugging step: Check the number of rows fetched
    if (empty($reviews)) {
        echo json_encode(['error' => 'No reviews found']);
    } else {
        echo json_encode($reviews);
    }
} catch (Exception $e) {
    // Return an error response
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    // Close the database connection if it exists
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
