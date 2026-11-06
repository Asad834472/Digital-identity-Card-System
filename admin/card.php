<?php
require_once __DIR__ . '/../functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('Invalid identity card.');
}

$statement = $pdo->prepare("SELECT * FROM students WHERE id = ? AND status = 'approved'");
$statement->execute([$id]);
$student = $statement->fetch();
if (!$student) {
    http_response_code(404);
    exit('Approved identity card not found.');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Identity card — <?= h($student['full_name']) ?></title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="card-page">
<header class="site-header no-print">
    <a class="brand" href="index.php">Digital Identity Admin</a>
    <div class="header-actions">
        <a class="header-link" href="review.php?id=<?= (int) $student['id'] ?>">Back</a>
        <button class="small-button" type="button" onclick="window.print()">Print card</button>
    </div>
</header>
<main class="card-page-content">
    <article class="identity-card">
        <div class="card-top">
            <div class="card-emblem">DI</div>
            <div><p class="card-overline">STUDENT IDENTITY</p><h1>Digital Identity Card</h1></div>
        </div>
        <div class="card-main">
            <?php if ($student['photo_path']): ?>
                <img class="card-photo" src="../<?= h($student['photo_path']) ?>" alt="Student photo">
            <?php else: ?>
                <div class="card-photo photo-placeholder"><?= h(strtoupper(substr($student['full_name'], 0, 1))) ?></div>
            <?php endif; ?>
            <div class="card-info">
                <h2><?= h($student['full_name']) ?></h2>
                <p class="card-number"><?= h($student['card_number']) ?></p>
                <dl>
                    <div><dt>Student ID</dt><dd><?= h($student['student_id']) ?></dd></div>
                    <div><dt>Department</dt><dd><?= h($student['department']) ?></dd></div>
                    <div><dt>Program</dt><dd><?= h($student['program']) ?></dd></div>
                    <div><dt>Status</dt><dd>Active student</dd></div>
                </dl>
            </div>
            <div class="qr-area">
                <div id="qrcode" aria-label="QR code for identity verification"></div>
                <span>Scan to verify</span>
            </div>
        </div>
        <div class="card-bottom"><span>Official digital student identity</span><span>Verify this card before use</span></div>
    </article>
    <p class="card-note no-print">Print with your browser’s print dialog. QR generation requires an internet connection to load the QR library.</p>
</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    const verifyUrl = new URL('../verify.php', window.location.href);
    verifyUrl.searchParams.set('code', <?= json_encode($student['public_code'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
    if (typeof QRCode === 'undefined') {
        document.getElementById('qrcode').textContent = 'QR unavailable';
    } else {
        new QRCode(document.getElementById('qrcode'), {
            text: verifyUrl.href,
            width: 116,
            height: 116,
            colorDark: '#102c42',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    }
</script>
</body>
</html>
