<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once("connect.php");

function updateOrderStatus($conn, $order_item_id, $cancel_note)
{
    try {
        $new_status = "Cancelled";
        $stmt = $conn->prepare("UPDATE order_items 
                                SET order_status = ?, order_note = ?
                                WHERE order_item_id = ?");
        $stmt->bind_param("ssi", $new_status, $cancel_note, $order_item_id);

        if ($stmt->execute()) {
            return ["status" => "success", "message" => "Order cancelled successfully"];
        } else {
            return ["status" => "error", "message" => "Failed to cancel the order: " . $stmt->error];
        }
    } catch (Exception $e) {
        return ["status" => "error", "message" => $e->getMessage()];
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $data = json_decode(file_get_contents("php://input"), true);

    if (json_last_error() !== JSON_ERROR_NONE || !isset($data['order_item_id']) || !isset($data['cancel_note'])) {
        echo json_encode(["status" => "error", "message" => "Invalid or missing input"]);
        exit;
    }

    $order_item_id = $data['order_item_id'];
    $cancel_note = $data['cancel_note'];

    $result = updateOrderStatus($conn, $order_item_id, $cancel_note);
    echo json_encode($result);
}
