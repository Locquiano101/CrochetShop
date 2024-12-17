<?php
include_once("connect.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the file is uploaded
    if (isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] == 0) {
        // Set upload directory
        $targetDirectory = $_SERVER['DOCUMENT_ROOT'] . "/img/";

        // Check if the directory exists, if not create it
        if (!is_dir($targetDirectory)) {
            if (!mkdir($targetDirectory, 0777, true)) {
                die('Failed to create directories...');
            }
        }

        // Get file information
        $fileName = $_FILES['fileUpload']['name'];
        $fileTmpName = $_FILES['fileUpload']['tmp_name'];
        $fileSize = $_FILES['fileUpload']['size'];
        $fileError = $_FILES['fileUpload']['error'];
        $fileType = $_FILES['fileUpload']['type'];

        // Define allowed file types
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

        // Check if file type is allowed
        if (in_array($fileType, $allowedTypes)) {
            // Generate a unique file name
            $uniqueFileName = uniqid('', true) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);

            // Set the full destination path
            $destination = $targetDirectory . $uniqueFileName;

            // Move the uploaded file to the target directory
            if (move_uploaded_file($fileTmpName, $destination)) {
                // Prepare product data for insertion
                $prodName = $_POST['prod_name'];
                $prodDescription = $_POST['prod_description'];
                $prodPrice = $_POST['prod_price'];
                $prodStock = $_POST['prod_stock'];

                // Use prepared statements to avoid SQL injection
                $sql = "INSERT INTO products (prod_img_file, prod_name, prod_description, prod_price, prod_stock_qty) 
                        VALUES (?, ?, ?, ?, ?)";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssis", $uniqueFileName, $prodName, $prodDescription, $prodPrice, $prodStock);

                if ($stmt->execute()) {
                    $message = "Product uploaded and data inserted successfully!";
                    // Optionally, you can also display the unique file name in the message
                } else {
                    $message = "Error: " . $stmt->error;
                }

                $stmt->close();
            } else {
                $message = "Error uploading the file.";
            }
        } else {
            $message = "Invalid file type. Only JPG, PNG, and GIF files are allowed.";
        }
    } else {
        $message = "No file uploaded or there was an error with the upload.";
    }
}

// Output the message
echo $message;

// Close connection
$conn->close();
