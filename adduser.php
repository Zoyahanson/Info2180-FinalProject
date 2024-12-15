<?php
session_start();
include_once 'config/database.php';
include_once 'models/user.php';

use app\models\User;
User::setConnection($conn);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = filter_input(INPUT_POST, "firstname", FILTER_SANITIZE_STRING);
    $lastname = filter_input(INPUT_POST, "lastname", FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_STRING);
    $role = filter_input(INPUT_POST, "role", FILTER_SANITIZE_STRING);

    if ($role !== 'Admin' && $role !== 'Member') {
        $error_message = "Invalid role selected.";
    }

    if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/', $password)) {
        $error_message = "Password must be at least 8 characters long, include one uppercase letter, one lowercase letter, and one number.";
    }

    if (!$error_message) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO Users (firstname, lastname, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $firstname, $lastname, $email, $hashed_password, $role);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $success_message = "New user added successfully!";
            } else {
                $error_message = "Failed to add the new user. Please try again.";
            }

            $stmt->close();
        } catch (Exception $e) {
            $error_message = "An error occurred: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐬 Dolphin CRM | Add User</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <header>
        <h1>🐬 Dolphin CRM - Add New User</h1>
    </header>

    <nav>
        <ul>
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="add_contact.php">New Contact</a></li>
            <li><a href="users.php">Users</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <main>
        <h2>New User</h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" required value="<?php echo isset($_POST['firstname']) ? htmlspecialchars($_POST['firstname']) : ''; ?>">

            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" required value="<?php echo isset($_POST['lastname']) ? htmlspecialchars($_POST['lastname']) : ''; ?>">

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="">Select Role</option>
                <option value="Admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="Member" <?php echo (isset($_POST['role']) && $_POST['role'] === 'Member') ? 'selected' : ''; ?>>Member</option>
            </select>

            <button type="submit">Save</button>

            <?php if ($success_message): ?>
                <div class="success-message"><?php echo $success_message; ?></div>
            <?php elseif ($error_message): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
        </form>
    </main>
</div>
</body>
</html>
