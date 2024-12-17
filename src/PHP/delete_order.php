<?php
header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['order_id'])) {
    $order_id = $input['order_id'];

    // Your database connection here
    $conn = new mysqli("localhost", "username", "password", "database");

    if ($conn->connect_error) {
        echo json_encode(['error' => 'Database connection failed']);
        exit;
    }

    $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => 'Failed to delete order']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'Invalid input']);
}
