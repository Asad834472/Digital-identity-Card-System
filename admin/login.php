<?php
require_once __DIR__ . '/../functions.php';

if (!empty($_SESSION['admin_id'])) {
    redirect('index.php');
}

$flash = take_flash();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $statement = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = ?');
    $statement->execute([$username]);
    $admin = $statement->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        redirect('index.php');
    }

    $error = 'Invalid username or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin sign in</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="../index.php">Digital Identity</a>
    <a class="header-link" href="../index.php">Registration</a>
</header>
<main class="page-shell narrow">
    <section class="panel">
        <p class="eyebrow">ADMINISTRATION</p>
        <h1>Sign in</h1>
        <?php if ($flash): ?><div class="notice <?= h($flash['type']) ?>"><?= h($flash['message']) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="notice error"><?= h($error) ?></div><?php endif; ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <label>Username
                <input type="text" name="username" autocomplete="username" required>
            </label>
            <label>Password
                <input type="password" name="password" autocomplete="current-password" required>
            </label>
            <button type="submit">Sign in</button>
        </form>
        <?php if ((int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn() === 0): ?>
            <p class="form-footnote"><a href="../setup_admin.php">Set up the first administrator</a></p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
