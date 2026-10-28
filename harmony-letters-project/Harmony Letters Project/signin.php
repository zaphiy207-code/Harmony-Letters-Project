<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_connect.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . (
        ($_SESSION['role'] ?? '') === 'admin'
            ? 'admin.php'
            : 'index.html'
    ));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $identity = trim($_POST['identity'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        'SELECT user_id, username, password_hash, role
         FROM users
         WHERE username = ? OR email = ?
         LIMIT 1'
    );
    $stmt->execute([$identity, $identity]);
    $user = $stmt->fetch();

    if (
        $user &&
        password_verify($password, $user['password_hash'])
    ) {
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int) $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        header('Location: ' . (
            $user['role'] === 'admin'
                ? 'admin.php'
                : 'index.html'
        ));
        exit;
    }

    $error = 'Incorrect username/email or password.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign In | Harmony Letters Project</title>
<link rel="stylesheet" href="harmony.css">
</head>
<body>
<div id="stars"></div>
<main class="auth-page">
    <h1>Sign In</h1>

    <?php if (isset($_GET['registered'])): ?>
        <p>Your account was created. You can now sign in.</p>
    <?php endif; ?>

    <?php if (isset($_GET['logged_out'])): ?>
        <p>You have been signed out.</p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p role="alert"><?= h($error) ?></p>
    <?php endif; ?>

    <form method="post" action="signin.php">
        <input type="hidden" name="csrf_token"
               value="<?= h($_SESSION['csrf_token']) ?>">

        <label for="identity">Username or email</label>
        <input id="identity" name="identity" required
               autocomplete="username">

        <label for="password">Password</label>
        <input id="password" name="password" type="password"
               required autocomplete="current-password">

        <button type="submit">Sign In</button>
    </form>

    <p>New here? <a href="createacc.php">Create an account</a></p>
    <p><a href="index.html">Return Home</a></p>
<script src="js/stars.js"></script>
</main>
</body>
</html>
