<?php
include_once("connect.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if product ID is provided
    if (isset($_POST['product_id'])) {
        $productId = intval($_POST['product_id']); // Sanitize input

        // Get the file name of the product image to delete
        $sqlGetFile = "SELECT prod_img_file FROM products WHERE prod_id = ?";
        $stmtGetFile = $conn->prepare($sqlGetFile);
        $stmtGetFile->bind_param("i", $productId);
        $stmtGetFile->execute();
        $result = $stmtGetFile->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $filePath = $_SERVER['DOCUMENT_ROOT'] . "/img/" . $row['prod_img_file'];

            // Delete the image file if it exists
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Delete the product record from the database
            $sqlDelete = "DELETE FROM products WHERE prod_id = ?";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bind_param("i", $productId);

            if ($stmtDelete->execute()) {
                echo "Product deleted successfully.";
            } else {
                echo "Error deleting product: " . $stmtDelete->error;
            }

            $stmtDelete->close();
        } else {
            echo "Product not found.";
        }

        $stmtGetFile->close();
    } else {
        echo "No product ID provided.";
    }
}

// Close the database connection
$conn->close();
