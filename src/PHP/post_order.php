<?php
include_once("connect.php");

// Get the JSON input
$order_data = json_decode(file_get_contents("php://input"), true);

// Debug: Log the raw input
error_log("Raw Input: " . file_get_contents("php://input"));
error_log("Parsed Data: " . print_r($order_data, true));

// Check if JSON is parsed successfully
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON format"]);
    exit;
}

// Validate the order_data
$required_fields = [
    'customer_id',
    'prod_id',
    'shipping_address',
    'product_name',
    'product_price',
    'product_qty'
];

foreach ($required_fields as $field) {
    if (empty($order_data[$field])) {
        echo json_encode(["status" => "error", "message" => "Missing or empty field: $field"]);
        exit;
    }
}

if (
    !is_numeric($order_data['customer_id']) || !is_numeric($order_data['prod_id']) ||
    !is_numeric($order_data['product_price']) || !is_numeric($order_data['product_qty'])
) {
    echo json_encode(["status" => "error", "message" => "Invalid data type"]);
    exit;
}

// Sanitize input
$order_user_id = intval($order_data["customer_id"]);
$order_product_id = intval($order_data["prod_id"]);
$order_shipping_address = $conn->real_escape_string($order_data["shipping_address"]);
$order_product_name = $conn->real_escape_string($order_data["product_name"]);
$order_product_price = floatval($order_data["product_price"]);
$order_product_qty = intval($order_data["product_qty"]);
$order_status = "In Cart";

try {
    // Check if the customer has a pending order
    $check_order_sql = "
        SELECT o.order_id, o.total_amount
        FROM orders o
        JOIN order_items oi ON o.order_id = oi.order_id
        WHERE o.customer_id = ? AND oi.order_status = 'pending'
    ";
    $check_stmt = $conn->prepare($check_order_sql);
    $check_stmt->bind_param("i", $order_user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $order_id = $row['order_id'];
        $new_total = $row['total_amount'] + ($order_product_price * $order_product_qty);

        // Update order total amount
        if (!updateOrderTotal($order_id, $new_total)) {
            throw new Exception("Failed to update order total amount");
        }

        // Check if the product exists in the order items
        if (!updateOrderItem($order_id, $order_product_id, $order_product_qty, $order_product_price, $order_status)) {
            throw new Exception("Failed to update or add order item");
        }

        echo json_encode(["status" => "success", "message" => "Order item updated successfully"]);
    } else {
        // Insert new order and item
        $order_id = insertNewOrder($order_user_id, $order_shipping_address, $order_product_price, $order_product_qty);

        if ($order_id && insertNewOrderItem($order_id, $order_product_id, $order_product_qty, $order_product_price, $order_status)) {
            echo json_encode(["status" => "success", "message" => "Order and item added successfully"]);
        } else {
            throw new Exception("Failed to create order or add item");
        }
    }

    $check_stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

function updateOrderTotal($order_id, $new_total)
{
    global $conn;
    $update_order_sql = "UPDATE orders SET total_amount = ? WHERE order_id = ?";
    $update_order_stmt = $conn->prepare($update_order_sql);
    $update_order_stmt->bind_param("di", $new_total, $order_id);
    return $update_order_stmt->execute();
}

function updateOrderItem($order_id, $prod_id, $order_product_qty, $order_product_price, $order_status)
{
    global $conn;
    $item_check_sql = "
        SELECT quantity, total_price, order_status
        FROM order_items
        WHERE order_id = ? AND prod_id = ? AND order_status IN ('Pending', 'Delivered')
    ";
    $item_check_stmt = $conn->prepare($item_check_sql);
    $item_check_stmt->bind_param("ii", $order_id, $prod_id);
    $item_check_stmt->execute();
    $item_result = $item_check_stmt->get_result();

    if ($item_result && $item_result->num_rows > 0) {
        $item_row = $item_result->fetch_assoc();
        $new_quantity = $item_row['quantity'] + $order_product_qty;
        $new_total_price = $new_quantity * $order_product_price;

        $update_item_sql = "
            UPDATE order_items
            SET quantity = ?, total_price = ?, order_status = ?
            WHERE order_id = ? AND prod_id = ?
        ";
        $update_item_stmt = $conn->prepare($update_item_sql);
        $update_item_stmt->bind_param("idsii", $new_quantity, $new_total_price, $order_status, $order_id, $prod_id);
        return $update_item_stmt->execute();
    } else {
        return insertNewOrderItem($order_id, $prod_id, $order_product_qty, $order_product_price, $order_status);
    }
}

function insertNewOrder($customer_id, $shipping_address, $product_price, $product_qty)
{
    global $conn;
    $total_amount = $product_price * $product_qty;
    $insert_order_sql = "INSERT INTO orders (customer_id, total_amount, shipping_address) VALUES (?, ?, ?)";
    $insert_order_stmt = $conn->prepare($insert_order_sql);
    $insert_order_stmt->bind_param("ids", $customer_id, $total_amount, $shipping_address);

    if ($insert_order_stmt->execute()) {
        return $conn->insert_id;
    }
    return false;
}

function insertNewOrderItem($order_id, $prod_id, $product_qty, $product_price, $order_status)
{
    global $conn;
    $total_price = $product_qty * $product_price;
    $insert_item_sql = "
        INSERT INTO order_items (order_id, prod_id, order_status, quantity, product_price, total_price)
        VALUES (?, ?, ?, ?, ?, ?)
    ";
    $insert_item_stmt = $conn->prepare($insert_item_sql);
    $insert_item_stmt->bind_param("iisidd", $order_id, $prod_id, $order_status, $product_qty, $product_price, $total_price);

    return $insert_item_stmt->execute();
}
