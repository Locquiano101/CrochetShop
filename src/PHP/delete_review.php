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

    // Check if the request method is DELETE
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        throw new Exception("Invalid request method.");
    }

    // Get the review ID from the request
    parse_str(file_get_contents("php://input"), $data);
    if (!isset($data['rev_id'])) {
        throw new Exception("Review ID is required.");
    }

    $rev_id = $conn->real_escape_string($data['rev_id']);

    // SQL query to delete the review
    $sql = "DELETE FROM reviews WHERE rev_id = '$rev_id'";

    if ($conn->query($sql) === false) {
        throw new Exception("Failed to delete review: " . $conn->error);
    }

    echo json_encode(['success' => true, 'message' => 'Review deleted successfully.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
