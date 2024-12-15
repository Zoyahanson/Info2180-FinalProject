<?php
session_start();
include_once 'config/database.php'; 
include_once 'models/user.php';    

use app\models\User;
User::setConnection($conn); 


if (!isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
    session_start();
}

$error_message = ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);
    $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

    $user = User::isValidCredentials($email, $password);

    if ($user) {
        
        $_SESSION['user_id'] = $user->getId();
        $_SESSION['firstname'] = $user->getFirstname();
        $_SESSION['lastname'] = $user->getLastname();
        $_SESSION['email'] = $user->getEmail();
        $_SESSION['role'] = $user->getRole();

        session_regenerate_id(true); 
        header('Location: ./dashboard.php'); 
        exit;
    } else {
        $error_message = "Invalid credentials";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🐬 Dolphin CRM | Login</title>
    link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="main">
    <header>
        <div class="dolphin">🐬 Dolphin CRM</div>
    </header>
    <br><br><br><br><br>

    <div class="login">
        <form class="login-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <h1>Login</h1>
            <div class="form-content">
                <input type="email" id="email" name="email" placeholder="Email address" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
            </div>
            <div class="form-content">
                <input type="password" id="password" name="password" placeholder="Password" required />
            </div>
            <button type="submit">Login</button>
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <p class="text">Copyright &copy; <?php echo date("Y"); ?> Dolphin CRM</p>
        </form>
    </div>
</div>
</body>
</html>