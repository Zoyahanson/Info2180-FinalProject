<?php
namespace app\models;

public static function isValidCredentials($email, $password) {
    global $conn;
    $sql = "SELECT * FROM Users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return new User($user); // Assuming the User model has a constructor
        }
    }
    return false; 
}

