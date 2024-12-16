<?php
session_start();
require 'db_connection.php';

if (isset($_POST['submit'])) {
    try {
        $title = htmlspecialchars(trim($_POST['title']));
        $first_name = htmlspecialchars(trim($_POST['first_name']));
        $last_name = htmlspecialchars(trim($_POST['last_name']));
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $telephone = htmlspecialchars(trim($_POST['telephone']));
        $company = htmlspecialchars(trim($_POST['company']));
        $type = htmlspecialchars(trim($_POST['type']));
        $assigned_to = intval($_POST['assigned_to']);
        $created_by = $_SESSION['user_id'] ?? 1; 

        if (!$email) {
            throw new Exception("Invalid email address.");
        }

        $stmt = $pdo->prepare("INSERT INTO contacts 
            (title, first_name, last_name, email, telephone, company, type, assigned_to, created_by, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");

        $stmt->execute([
            $title, $first_name, $last_name, $email, $telephone, $company, $type, $assigned_to, $created_by
        ]);

        $_SESSION['success'] = "Contact created successfully!";
        header("Location: new_contact.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: new_contact.php");
        exit();
    }
} else {
    header("Location: new_contact.php");
    exit();
}
?>
