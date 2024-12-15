<?php
// login.php
include_once("connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and retrieve form data
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);

    $response = [
        "status" => "error",
        "message" => "Invalid email or password."
    ];

    // Prepare the SQL query to fetch user data
    $sql = "SELECT * FROM customers WHERE user_email = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['user_password'])) {
                session_start();
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = "{$user['user_firstName']} {$user['user_lastName']}";

                $response = [
                    "status" => "success",
                    "message" => "Login successful.",
                    "customers" => [
                        "user_id" => $user['user_id'],
                        "user_name" => "{$user['user_firstName']} {$user['user_lastName']}",
                        "user_address" => $user['user_address']
                    ]
                ];
            }
        }
        $stmt->close();
    }

    $conn->close();
    echo json_encode($response);
}
