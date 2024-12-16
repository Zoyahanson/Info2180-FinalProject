<?php
session_start();
require 'db_connection.php';


$stmt = $pdo->query("SELECT id, CONCAT(title, ' ', first_name, ' ', last_name) AS name FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact</title>
</head>
<body>
    <h1>New Contact</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    
    <form method="POST" action="create_contact.php">
        <label>Title</label>
        <select name="title" required>
            <option value="Mr">Mr</option>
            <option value="Mrs">Mrs</option>
            <option value="Ms">Ms</option>
            <option value="Dr">Dr</option>
            <option value="Prof">Prof</option>
        </select><br>

        <label>First Name</label>
        <input type="text" name="first_name" required><br>

        <label>Last Name</label>
        <input type="text" name="last_name" required><br>

        <label>Email</label>
        <input type="email" name="email" required><br>

        <label>Telephone</label>
        <input type="text" name="telephone"><br>

        <label>Company</label>
        <input type="text" name="company"><br>

        <label>Type</label>
        <select name="type" required>
            <option value="Sales Lead">Sales Lead</option>
            <option value="Support">Support</option>
        </select><br>

        <label>Assigned To</label>
        <select name="assigned_to" required>
            <?php foreach ($users as $user): ?>
                <option value="<?php echo $user['id']; ?>">
                    <?php echo htmlspecialchars($user['name']); ?>
                </option>
            <?php endforeach; ?>
        </select><br>

        <button type="submit" name="submit">Save</button>
    </form>
</body>
</html>

