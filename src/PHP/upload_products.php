<?php
include_once("connect.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the file is uploaded
    if (isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] == 0) {
        // Set upload directory
        $uploadDirectory = $_SERVER['DOCUMENT_ROOT'] . '/res/img/';

        // Get file information
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
            $destination = $uploadDirectory . $uniqueFileName;

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
                    echo `<script>console.log("$uniqueFileName")</script>`;
                    $message = "Product uploaded and data inserted successfully!";
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Upload Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex justify-center items-center h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-lg">
        <h1 class="text-3xl font-semibold text-center mb-6">Upload Product</h1>
        <?php if (isset($message)): ?>
            <div class="mb-4 text-center text-red-500 font-medium">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        <form
            action=""
            method="post"
            enctype="multipart/form-data">
            <div class="mb-4">
                <label for="prod_name" class="block text-sm font-medium text-gray-700">Product Name:</label>
                <input
                    type="text"
                    name="prod_name"
                    class="mt-1 p-2 w-full border border-gray-300 rounded-md"
                    required />
            </div>

            <div class="mb-4">
                <label for="prod_description" class="block text-sm font-medium text-gray-700">Product Description:</label>
                <textarea
                    name="prod_description"
                    class="mt-1 p-2 w-full border border-gray-300 rounded-md"
                    rows="4"></textarea>
            </div>

            <div class="mb-4">
                <label for="prod_price" class="block text-sm font-medium text-gray-700">Product Price:</label>
                <input
                    type="number"
                    name="prod_price"
                    class="mt-1 p-2 w-full border border-gray-300 rounded-md"
                    step="0.01"
                    required />
            </div>

            <div class="mb-4">
                <label for="prod_stock" class="block text-sm font-medium text-gray-700">Product Stock:</label>
                <input
                    type="number"
                    name="prod_stock"
                    class="mt-1 p-2 w-full border border-gray-300 rounded-md"
                    required />
            </div>

            <div class="mb-4">
                <label for="fileUpload" class="block text-sm font-medium text-gray-700">Choose an image to upload:</label>
                <input
                    type="file"
                    name="fileUpload"
                    class="mt-1 p-2 w-full border border-gray-300 rounded-md"
                    required />
            </div>

            <div class="flex justify-center">
                <input
                    type="submit"
                    value="Upload Product"
                    class="px-6 py-2 bg-blue-500 text-white font-medium rounded-md hover:bg-blue-600 cursor-pointer" />
            </div>
        </form>
    </div>
</body>

</html>