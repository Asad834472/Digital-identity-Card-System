<?php
require_once __DIR__ . '/functions.php';

$code = $_GET['code'] ?? '';
$student = null;
if (is_string($code) && preg_match('/^[a-f0-9]{64}$/', $code)) {
    $statement = $pdo->prepare(
        "SELECT full_name, student_id, department, program, photo_path, card_number, status
         FROM students WHERE public_code = ? AND status = 'approved'"
    );
    $statement->execute([$code]);
    $student = $statement->fetch() ?: null;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Identity verification</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header"><a class="brand" href="index.php">Digital Identity</a></header>
<main class="page-shell narrow verify-shell">
    <?php if ($student): ?>
        <section class="panel verification-card">
            <div class="verified-mark" aria-hidden="true">✓</div>
            <p class="eyebrow">IDENTITY VERIFIED</p>
            <h1>Active student</h1>
            <?php if ($student['photo_path']): ?><img class="verify-photo" src="<?= h($student['photo_path']) ?>" alt="Student photo"><?php endif; ?>
            <dl class="detail-list public-details">
                <div><dt>Name</dt><dd><?= h($student['full_name']) ?></dd></div>
                <div><dt>Student ID</dt><dd><?= h($student['student_id']) ?></dd></div>
                <div><dt>Card number</dt><dd><?= h($student['card_number']) ?></dd></div>
                <div><dt>Department</dt><dd><?= h($student['department']) ?></dd></div>
                <div><dt>Program</dt><dd><?= h($student['program']) ?></dd></div>
            </dl>
            <p class="verification-footnote">This record is currently approved in the Digital Identity system.</p>
        </section>
    <?php else: ?>
        <section class="panel verification-card">
            <div class="invalid-mark" aria-hidden="true">!</div>
            <p class="eyebrow">VERIFICATION FAILED</p>
            <h1>Identity not verified</h1>
            <p>This QR code is invalid or the card is no longer active. Contact the institution for assistance.</p>
        </section>
    <?php endif; ?>
</main>
</body>
</html>
