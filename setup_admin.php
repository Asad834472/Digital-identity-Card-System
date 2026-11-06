<?php
require_once __DIR__ . '/functions.php';

$adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if ($adminCount > 0) {
    redirect('admin/login.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (!preg_match('/^[A-Za-z0-9._-]{3,80}$/', $username)) {
        $error = 'Username must be 3–80 characters and use letters, numbers, dots, underscores, or hyphens.';
    } elseif (strlen($password) < 12) {
        $error = 'Choose a password with at least 12 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'The passwords do not match.';
    } else {
        try {
            $statement = $pdo->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
            $statement->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
            set_flash('success', 'Admin account created. Sign in to manage applications.');
            redirect('admin/login.php');
        } catch (PDOException $exception) {
            error_log($exception->getMessage());
            $error = 'The admin account could not be created. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set up administrator</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="site-header"><a class="brand" href="index.php">Digital Identity</a></header>
<main class="page-shell narrow">
    <section class="panel">
        <p class="eyebrow">ONE-TIME SETUP</p>
        <h1>Create the first administrator</h1>
        <p>Only works before an admin account exists. Use a strong password and keep it private.</p>
        <?php if ($error !== ''): ?><div class="notice error"><?= h($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <label>Username
                <input type="text" name="username" minlength="3" maxlength="80" autocomplete="username" required>
            </label>
            <label>Password (at least 12 characters)
                <input type="password" name="password" minlength="12" autocomplete="new-password" required>
            </label>
            <label>Confirm password
                <input type="password" name="confirm_password" minlength="12" autocomplete="new-password" required>
            </label>
            <button type="submit">Create admin account</button>
        </form>
    </section>
</main>
</body>
</html>
