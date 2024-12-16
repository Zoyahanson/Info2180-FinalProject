<?php
session_start();

use app\models\User;

$pdo = new PDO('mysql:host=localhost;dbname=your_database', 'username', 'password');

$contactId = null;

if (isset($_GET['id'])) {
    $contactId = $_GET['id'];
    $_SESSION['contactId'] = $contactId;
} else {
    $contactId = $_SESSION['contactId'] ?? null;
}

if (!$contactId) {
    die('No contact selected.');
}


User::setConnection($pdo);


$stmt = $pdo->prepare('SELECT * FROM contacts WHERE id = :contactId');
$stmt->bindParam(':contactId', $contactId, PDO::PARAM_INT);
$stmt->execute();
$contact = $stmt->fetch(PDO::FETCH_OBJ);
if (!$contact) {
    die('Contact not found.');
}


$stmtUser = $pdo->prepare('SELECT * FROM users WHERE id = :userId');
$stmtUser->bindParam(':userId', $contact->created_by, PDO::PARAM_INT);
$stmtUser->execute();
$createdByUser = $stmtUser->fetch(PDO::FETCH_OBJ);


$response = [];
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $comment = htmlspecialchars(trim($_POST['comment']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    if ($contactId && !empty($comment)) {
        $stmtNote = $pdo->prepare('INSERT INTO notes (contact_id, comment, created_by, created_at) VALUES (:contactId, :comment, :createdBy, NOW())');
        $stmtNote->bindParam(':contactId', $contactId, PDO::PARAM_INT);
        $stmtNote->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmtNote->bindParam(':createdBy', $_SESSION['user_id'], PDO::PARAM_INT);
        if ($stmtNote->execute()) {
            
            $stmtUpdateTimestamp = $pdo->prepare('UPDATE contacts SET updated_at = NOW() WHERE id = :contactId');
            $stmtUpdateTimestamp->bindParam(':contactId', $contactId, PDO::PARAM_INT);
            $stmtUpdateTimestamp->execute();

            $response['status'] = 'success';
            $response['message'] = 'Note added successfully';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to add note.';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Failed to add note. Comment is required.';
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}


$stmtNotes = $pdo->prepare('SELECT * FROM notes WHERE contact_id = :contactId ORDER BY created_at DESC');
$stmtNotes->bindParam(':contactId', $contactId, PDO::PARAM_INT);
$stmtNotes->execute();
$notes = $stmtNotes->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Notes</title>
    <link rel="stylesheet" href="assets/css/contactNotes.css">
</head>
<body>
<div class="main-contact">
    <div class="note-header">
        <div class="header-info">
            <h1><?php echo htmlspecialchars($contact->title . '. ' . $contact->first_name . ' ' . $contact->last_name); ?></h1>
            <p class="date-info">Created on <?php echo htmlspecialchars($contact->created_at . ' by ' . $createdByUser->first_name . ' ' . $createdByUser->last_name); ?></p>
            <p class="date-info">Updated on <?php echo htmlspecialchars($contact->updated_at); ?></p>
        </div>
        <div class="header-buttons">
            <button class="add-btn" id="btnAssignToMe">
                Assign to me
            </button>
            <button class="add-btn" id="btnSwitchType">
                Switch to <?php echo $contact->type == 'business' ? 'individual' : 'business'; ?>
            </button>
        </div>
    </div>

    <div class="contact-info">
        <div class="info-row">
            <div class="info-item">
                <strong>Email</strong>
                <p><?php echo htmlspecialchars($contact->email); ?></p>
            </div>
            <div class="info-item">
                <strong>Telephone</strong>
                <p><?php echo htmlspecialchars($contact->telephone); ?></p>
            </div>
        </div>
        <div class="info-row">
            <div class="info-item">
                <strong>Company</strong>
                <p><?php echo htmlspecialchars($contact->company); ?></p>
            </div>
            <div class="info-item">
                <strong>Assigned To</strong>
                <p><?php echo htmlspecialchars(User::getUserById($contact->assigned_to)->getFirstName() . ' ' . User::getUserById($contact->assigned_to)->getLastName()); ?></p>
            </div>
        </div>
    </div>

    <div class="notes-section">
        <h3>Notes</h3><br>
        <div class="notes-list">
            <?php foreach ($notes as $note): ?>
                <div class="note">
                    <p><strong><?php echo htmlspecialchars(User::getUserById($note->created_by)->getFirstName() . ' ' . User::getUserById($note->created_by)->getLastName()); ?></strong></p>
                    <p><?php echo nl2br(htmlspecialchars($note->comment)); ?></p>
                    <p class="note-date"><?php echo htmlspecialchars($note->created_at); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="add-note">
        <h3>Add a note about <?php echo htmlspecialchars($contact->first_name); ?></h3>
        <form id="noteForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <textarea id="comment" name="comment" placeholder="Enter details here" required></textarea>
            <button type="submit">Add Note</button>
        </form>
    </div>
</div>
</body>
</html>
