<?php
require_once __DIR__ . '/../functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('Invalid application.');
}

$statement = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$statement->execute([$id]);
$student = $statement->fetch();
if (!$student) {
    http_response_code(404);
    exit('Application not found.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Review application</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="index.php">Digital Identity Admin</a>
    <a class="header-link" href="index.php">Back to applications</a>
</header>
<main class="page-shell narrow">
    <section class="panel">
        <p class="eyebrow">APPLICATION REVIEW</p>
        <div class="detail-heading">
            <?php if ($student['photo_path']): ?>
                <img class="student-photo" src="../<?= h($student['photo_path']) ?>" alt="Student photo">
            <?php endif; ?>
            <div><h1><?= h($student['full_name']) ?></h1><span class="status-badge <?= h($student['status']) ?>"><?= h(ucfirst($student['status'])) ?></span></div>
        </div>
        <dl class="detail-list">
            <div><dt>Student ID</dt><dd><?= h($student['student_id']) ?></dd></div>
            <div><dt>Email</dt><dd><?= h($student['email']) ?></dd></div>
            <div><dt>Phone</dt><dd><?= h($student['phone']) ?></dd></div>
            <div><dt>Department</dt><dd><?= h($student['department']) ?></dd></div>
            <div><dt>Program</dt><dd><?= h($student['program']) ?></dd></div>
            <div><dt>Submitted</dt><dd><?= h(date('M j, Y g:i A', strtotime($student['submitted_at']))) ?></dd></div>
            <?php if ($student['rejection_reason']): ?><div><dt>Rejection reason</dt><dd><?= h($student['rejection_reason']) ?></dd></div><?php endif; ?>
        </dl>
        <p class="review-edit-link"><a class="button secondary" href="edit.php?id=<?= (int) $student['id'] ?>">Correct / edit details</a></p>
        <?php if ($student['status'] === 'pending'): ?>
            <div class="review-actions">
                <form action="action.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="student_id" value="<?= (int) $student['id'] ?>">
                    <input type="hidden" name="decision" value="approve">
                    <button type="submit">Approve and issue card</button>
                </form>
                <form action="action.php" method="post" class="reject-form">
                    <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                    <input type="hidden" name="student_id" value="<?= (int) $student['id'] ?>">
                    <input type="hidden" name="decision" value="reject">
                    <label class="sr-only" for="reason">Reason for rejection</label>
                    <input id="reason" type="text" name="reason" maxlength="500" placeholder="Reason (optional)">
                    <button class="danger" type="submit">Reject</button>
                </form>
            </div>
        <?php elseif ($student['status'] === 'approved'): ?>
            <a class="button" href="card.php?id=<?= (int) $student['id'] ?>">View / print identity card</a>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
