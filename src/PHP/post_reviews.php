<?php
include_once("connect.php");

// Set the response header to return JSON
header("Content-Type: application/json");

/**
 * Insert a review into the database.
 * 
 * @param object $conn The database connection.
 * @param int $customer_id The ID of the customer.
 * @param int $product_id The ID of the product.
 * @param int $rating The rating value.
 * @param string $feedback The comment or feedback.
 * @return array Response status and message.
 */
function submitRating($conn, $customer_id, $product_id, $rating, $feedback)
{
    try {
        $stmt = $conn->prepare("INSERT INTO reviews (customer_id, product_id, rev_rate, rev_comment) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param('iiis', $customer_id, $product_id, $rating, $feedback);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Rating submitted successfully!"];
        } else {
            throw new Exception("Failed to save rating: " . $stmt->error);
        }
    } catch (Exception $e) {
        return ["status" => "error", "message" => $e->getMessage()];
    } finally {
        $stmt->close();
    }
}

/**
 * Update the status of an order item.
 * 
 * @param object $conn The database connection.
 * @param int $order_item_id The ID of the order item.
 * @param string $new_status The new status of the order.
 * @return array Response status and message.
 */
function updateOrderStatus($conn, $order_item_id, $new_status)
{
    try {
        $stmt = $conn->prepare("UPDATE order_items SET order_status = ? WHERE order_item_id = ?");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param("si", $new_status, $order_item_id);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Order updated successfully"];
        } else {
            throw new Exception("Failed to update the order: " . $stmt->error);
        }
    } catch (Exception $e) {
        return ["status" => "error", "message" => $e->getMessage()];
    } finally {
        $stmt->close();
    }
}

/**
 * Main handler for POST requests.
 */
try {
    $data = json_decode(file_get_contents("php://input"), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON input");
    }

    // Start a transaction
    $conn->begin_transaction();

    // Determine action based on input
    if (isset($data['action'])) {
        switch ($data['action']) {
            case 'submit_rating_and_update_order':
                if (isset($data['customer_id'], $data['product_id'], $data['rating'], $data['comment'], $data['order_item_id'])) {
                    $customer_id = filter_var($data['customer_id'], FILTER_VALIDATE_INT);
                    $product_id = filter_var($data['product_id'], FILTER_VALIDATE_INT);
                    $rating = filter_var($data['rating'], FILTER_VALIDATE_INT);
                    $feedback = htmlspecialchars($data['comment'], ENT_QUOTES, 'UTF-8');
                    $order_item_id = filter_var($data['order_item_id'], FILTER_VALIDATE_INT);
                    $new_status = "Completed"; // Default status for updates

                    if (!$customer_id || !$product_id || !$rating || empty($feedback) || !$order_item_id) {
                        throw new Exception("Invalid input data");
                    }

                    // First, submit the rating
                    $review_response = submitRating($conn, $customer_id, $product_id, $rating, $feedback);
                    if ($review_response['status'] !== 'success') {
                        throw new Exception($review_response['message']);
                    }

                    // Then, update the order status
                    $order_response = updateOrderStatus($conn, $order_item_id, $new_status);
                    if ($order_response['status'] !== 'success') {
                        throw new Exception($order_response['message']);
                    }

                    // Commit transaction if both actions are successful
                    $conn->commit();

                    echo json_encode(["status" => "success", "message" => "Review submitted and order updated successfully!"]);
                } else {
                    throw new Exception("Missing required fields for submitting rating and updating order status");
                }
                break;

            default:
                throw new Exception("Invalid action specified");
        }
    } else {
        throw new Exception("No action specified");
    }
} catch (Exception $e) {
    // Rollback transaction if there's an error
    $conn->rollback();

    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} finally {
    $conn->close();
}
