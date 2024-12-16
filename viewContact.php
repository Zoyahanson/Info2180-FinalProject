<?php
session_start();
require 'db_connection.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Contact not found.";
    header('Location: dashboard.php');
    exit;
}

$contact_id = $_GET['id'];
$user_id = $_SESSION['user_id']; 


$stmt = $pdo->prepare("
    SELECT contacts.*, 
           creator.first_name AS creator_first_name, creator.last_name AS creator_last_name,
           assignee.first_name AS assignee_first_name, assignee.last_name AS assignee_last_name
    FROM contacts 
    LEFT JOIN users AS creator ON contacts.created_by = creator.id
    LEFT JOIN users AS assignee ON contacts.assigned_to = assignee.id
    WHERE contacts.id = ?
");
$stmt->execute([$contact_id]);
$contact = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contact) {
    $_SESSION['error'] = "Contact not found.";
    header('Location: dashboard.php');
    exit;
}


if (isset($_POST['assign_to_me'])) {
    $stmt = $pdo->prepare("UPDATE contacts SET assigned_to = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$user_id, $contact_id]);
    $_SESSION['success'] = "Contact successfully assigned to you.";
    header("Location: view_contact.php?id=$contact_id");
    exit;
}


if (isset($_POST['switch_type'])) {
    $new_type = ($contact['type'] === 'Sales Lead') ? 'Support' : 'Sales Lead';
    $stmt = $pdo->prepare("UPDATE contacts SET type = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$new_type, $contact_id]);
    $_SESSION['success'] = "Contact type updated to $new_type.";
    header("Location: view_contact.php?id=$contact_id");
    exit;
}


$note_stmt = $pdo->prepare("
    SELECT notes.comment, notes.created_at, users.first_name, users.last_name
    FROM notes 
    LEFT JOIN users ON notes.created_by = users.id
    WHERE notes.contact_id = ?
    ORDER BY notes.created_at DESC
");
$note_stmt->execute([$contact_id]);
$notes = $note_stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['add_note'])) {
    $note_comment = trim($_POST['note']);
    if (!empty($note_comment)) {
        $insert_note = $pdo->prepare("INSERT INTO notes (contact_id, created_by, comment, created_at) VALUES (?, ?, ?, NOW())");
        $insert_note->execute([$contact_id, $user_id, htmlspecialchars($note_comment)]);
        $_SESSION['success'] = "Note added successfully.";
        header("Location: view_contact.php?id=$contact_id");
        exit;
    } else {
        $_SESSION['error'] = "Note cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Details</title>
</head>
<body>
    <h1>Contact Details</h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>

   
    <p><strong>Title & Name:</strong> <?php echo htmlspecialchars($contact['title'] . ' ' . $contact['first_name'] . ' ' . $contact['last_name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($contact['email']); ?></p>
    <p><strong>Company:</strong> <?php echo htmlspecialchars($contact['company']); ?></p>
    <p><strong>Telephone:</strong> <?php echo htmlspecialchars($contact['telephone']); ?></p>
    <p><strong>Type:</strong> <?php echo htmlspecialchars($contact['type']); ?></p>
    <p><strong>Date Created:</strong> <?php echo $contact['created_at']; ?> by <?php echo htmlspecialchars($contact['creator_first_name'] . ' ' . $contact['creator_last_name']); ?></p>
    <p><strong>Last Updated:</strong> <?php echo $contact['updated_at']; ?></p>
    <p><strong>Assigned To:</strong> <?php echo htmlspecialchars($contact['assignee_first_name'] . ' ' . $contact['assignee_last_name']); ?></p>

    <form method="POST">
        <button type="submit" name="assign_to_me">Assign to Me</button>
    </form>

    <form method="POST">
        <button type="submit" name="switch_type">
            Switch to <?php echo ($contact['type'] === 'Sales Lead') ? 'Support' : 'Sales Lead'; ?>
        </button>
    </form>

    <h2>Notes</h2>
    <?php foreach ($notes as $note): ?>
        <div>
            <p><strong><?php echo htmlspecialchars($note['first_name'] . ' ' . $note['last_name']); ?></strong> (<?php echo $note['created_at']; ?>)</p>
            <p><?php echo nl2br(htmlspecialchars($note['comment'])); ?></p>
        </div>
        <hr>
    <?php endforeach; ?>

    <h3>Add a Note</h3>
    <form method="POST">
        <textarea name="note" rows="4" cols="50" placeholder="Add your note here..." required></textarea><br>
        <button type="submit" name="add_note">Save Note</button>
    </form>

    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
