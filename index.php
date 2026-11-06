<?php
require_once __DIR__ . '/functions.php';
$flash = take_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Identity Card Registration</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="index.php">Digital Identity</a>
    <a class="header-link" href="admin/login.php">Admin sign in</a>
</header>
<main class="page-shell">
    <section class="intro">
        <p class="eyebrow">STUDENT SERVICES</p>
        <h1>Apply for your digital identity card</h1>
        <p>Submit your details below. An administrator will review your application. Approved cards include a QR code that verifies your student identity.</p>
    </section>

    <section class="panel">
        <h2>Registration form</h2>
        <?php if ($flash): ?>
            <div class="notice <?= h($flash['type']) ?>"><?= h($flash['message']) ?></div>
        <?php endif; ?>
        <form action="submit.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <div class="form-grid">
                <label>Full name
                    <input type="text" name="full_name" maxlength="150" autocomplete="name" required>
                </label>
                <label>Student ID
                    <input type="text" name="student_id" maxlength="60" required>
                </label>
                <label>Email address
                    <input type="email" name="email" maxlength="190" autocomplete="email" required>
                </label>
                <label>Phone number
                    <input type="tel" name="phone" maxlength="30" autocomplete="tel" required>
                </label>
                <label>Department
                    <input type="text" name="department" maxlength="120" required>
                </label>
                <label>Program / degree
                    <input type="text" name="program" maxlength="120" required>
                </label>
                <label class="full-width">Photo (optional, JPG/PNG/WebP; max 2 MB)
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
                </label>
            </div>
            <button type="submit">Submit application</button>
        </form>
        <p class="form-footnote">Your application stays pending until it is reviewed by an administrator.</p>
    </section>
</main>
<footer class="site-footer">Digital Identity Card System</footer>
</body>
</html>
