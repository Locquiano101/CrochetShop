<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = ''; // Update with your database password
$database = 'your_database_name'; // Update with your database name

// Connect to the database
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $productName = $conn->real_escape_string($_POST['productName']);
    $productDescription = $conn->real_escape_string($_POST['prod_description']);
    $productPrice = (float) $_POST['productPrice'];
    $productStock = (int) $_POST['ProductStock'];
    $productId = (int) $_POST['productId']; // Pass product ID in the form (hidden field)

    // Check if a new image file was uploaded
    $imageUploaded = isset($_FILES['UploadNewProductImage']) && $_FILES['UploadNewProductImage']['error'] === UPLOAD_ERR_OK;

    if ($imageUploaded) {
        $imageName = $_FILES['UploadNewProductImage']['name'];
        $imageTmpName = $_FILES['UploadNewProductImage']['tmp_name'];
        $imageFolder = 'img/' . $imageName;

        // Move uploaded file to the destination folder
        if (!move_uploaded_file($imageTmpName, $imageFolder)) {
            die("Failed to upload the image.");
        }

        // Update product with new image
        $sql = "UPDATE products SET name = '$productName', description = '$productDescription', price = $productPrice, stock = $productStock, img = '$imageName' WHERE id = $productId";
    } else {
        // Update product without new image
        $sql = "UPDATE products SET name = '$productName', description = '$productDescription', price = $productPrice, stock = $productStock WHERE id = $productId";
    }

    // Execute the query
    if ($conn->query($sql) === TRUE) {
        echo "Product updated successfully.";
    } else {
        echo "Error updating product: " . $conn->error;
    }
}

$conn->close();
