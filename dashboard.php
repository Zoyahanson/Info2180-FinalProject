<?php
session_start();
require 'db_connection.php'; 


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$current_user_id = $_SESSION['user_id']; 


switch ($filter) {
    case 'sales':
        $query = "SELECT * FROM contacts WHERE type = 'SALES LEAD'";
        break;
    case 'support':
        $query = "SELECT * FROM contacts WHERE type = 'SUPPORT'";
        break;
    case 'assigned':
        $query = "SELECT * FROM contacts WHERE assigned_user_id = ?";
        break;
    default: 
        $query = "SELECT * FROM contacts";
}

$stmt = $pdo->prepare($query);


if ($filter == 'assigned') {
    $stmt->execute([$current_user_id]);
} else {
    $stmt->execute();
}

$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav>
        <ul>
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="new_contact.php">New Contact</a></li>
            <li><a href="users.php">Users</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <h1>Dashboard</h1>

    
    <div>
        <a href="dashboard.php?filter=all">All</a> |
        <a href="dashboard.php?filter=sales">Sales Leads</a> |
        <a href="dashboard.php?filter=support">Support</a> |
        <a href="dashboard.php?filter=assigned">Assigned to Me</a>
    </div>

  
    <a href="new_contact.php" style="float: right; margin-bottom: 10px;">+ Add Contact</a>

    
    <table border="1">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Company</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($contacts as $contact): ?>
                <tr>
                    <td><?= htmlspecialchars($contact['title'] . ' ' . $contact['full_name']) ?></td>
                    <td><?= htmlspecialchars($contact['email']) ?></td>
                    <td><?= htmlspecialchars($contact['company']) ?></td>
                    <td><?= htmlspecialchars($contact['type']) ?></td>
                    <td>
                        <a href="view_contact.php?id=<?= $contact['id'] ?>">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
