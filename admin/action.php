<?php
require_once __DIR__ . '/../functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
verify_csrf();

$id = filter_input(INPUT_POST, 'student_id', FILTER_VALIDATE_INT);
$decision = $_POST['decision'] ?? '';
if (!$id || !in_array($decision, ['approve', 'reject'], true)) {
    set_flash('error', 'Invalid application action.');
    redirect('index.php');
}

try {
    if ($decision === 'approve') {
        $publicCode = bin2hex(random_bytes(32));
        $cardNumber = 'DIC-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $statement = $pdo->prepare(
            "UPDATE students
             SET status = 'approved', public_code = ?, card_number = ?, reviewed_at = CURRENT_TIMESTAMP, rejection_reason = NULL
             WHERE id = ? AND status = 'pending'"
        );
        $statement->execute([$publicCode, $cardNumber, $id]);
        if ($statement->rowCount() !== 1) {
            set_flash('error', 'This application is no longer pending.');
            redirect('review.php?id=' . $id);
        }
        set_flash('success', 'Application approved and identity card issued.');
    } else {
        $reason = trim((string) ($_POST['reason'] ?? ''));
        $statement = $pdo->prepare(
            "UPDATE students SET status = 'rejected', rejection_reason = ?, reviewed_at = CURRENT_TIMESTAMP
             WHERE id = ? AND status = 'pending'"
        );
        $statement->execute([$reason !== '' ? $reason : null, $id]);
        if ($statement->rowCount() !== 1) {
            set_flash('error', 'This application is no longer pending.');
            redirect('review.php?id=' . $id);
        }
        set_flash('success', 'Application rejected.');
    }
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    set_flash('error', 'The application could not be updated. Please try again.');
}

redirect('review.php?id=' . $id);
