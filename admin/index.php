<?php
require_once __DIR__ . '/../functions.php';
require_admin();

$allowedStatuses = ['pending', 'approved', 'rejected'];
$status = $_GET['status'] ?? 'pending';
if (!in_array($status, $allowedStatuses, true)) {
    $status = 'pending';
}

$counts = $pdo->query("SELECT status, COUNT(*) AS total FROM students GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$statement = $pdo->prepare(
    'SELECT id, full_name, student_id, email, department, program, status, submitted_at
     FROM students WHERE status = ? ORDER BY submitted_at DESC'
);
$statement->execute([$status]);
$students = $statement->fetchAll();
$flash = take_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="index.php">Digital Identity Admin</a>
    <div class="header-actions">
        <span class="welcome">Signed in as <?= h($_SESSION['admin_username'] ?? 'admin') ?></span>
        <a class="header-link" href="logout.php">Sign out</a>
    </div>
</header>
<main class="page-shell wide">
    <section class="intro">
        <p class="eyebrow">APPLICATION MANAGEMENT</p>
        <h1>Student applications</h1>
        <p>Review submitted information and approve eligible students to issue a digital identity card.</p>
    </section>
    <?php if ($flash): ?><div class="notice <?= h($flash['type']) ?>"><?= h($flash['message']) ?></div><?php endif; ?>
    <nav class="tabs" aria-label="Filter applications">
        <?php foreach ($allowedStatuses as $item): ?>
            <a class="<?= $status === $item ? 'active' : '' ?>" href="?status=<?= h($item) ?>">
                <?= ucfirst($item) ?> <span><?= (int) ($counts[$item] ?? 0) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <section class="panel table-panel">
        <?php if (!$students): ?>
            <div class="empty-state">No <?= h($status) ?> applications.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Student</th><th>Student ID</th><th>Department / program</th><th>Submitted</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><strong><?= h($student['full_name']) ?></strong><small><?= h($student['email']) ?></small></td>
                            <td><?= h($student['student_id']) ?></td>
                            <td><?= h($student['department']) ?><small><?= h($student['program']) ?></small></td>
                            <td><?= h(date('M j, Y', strtotime($student['submitted_at']))) ?></td>
                            <td><a class="button secondary small-button" href="review.php?id=<?= (int) $student['id'] ?>">View application</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
