<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: views/logout.php");
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>🐬 Dolphin CRM</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Symbols+Outlined" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        window.loggedInUserId = <?php echo json_encode($_SESSION['user_id']); ?>;
    </script>
</head>
<body>
    <header>
        <div class="dolpin">🐬 Dolphin CRM</div>
    </header>
    <aside class="sidebar">
        <ul>
            <li><a class="sidebar-link" data-target="views/home.php"><span class="material-symbols-outlined">home</span>Home</a></li>
            <li><a class="sidebar-link" data-target="views/newContact.php"><span class="material-symbols-outlined">account_circle</span>New Contact</a></li>
            <?php if ($_SESSION['role'] === 'Admin') { ?>
                <!-- Only show the "Users" link for an Admin role -->
                <li><a class="sidebar-link" data-target="views/viewUsers.php"><span class="material-symbols-outlined">group</span>Users</a></li>
            <?php } ?>
            <hr>
            <li><a class="sidebar-link" data-target="views/logout.php"><span class="material-symbols-outlined">login</span>Logout</a></li>
        </ul>
    </aside>

    <!-- Message container for success or error messages -->
    <div id="temporaryMessage" class="message" style="display: none;"></div>

    <!-- Main Content -->
    <main class="content" id="mainContent">
        <!-- The Main Content is dynamically shown here -->
    </main>

<script src="assets/js/main.js" type="text/javascript"></script>
</body>
</html>