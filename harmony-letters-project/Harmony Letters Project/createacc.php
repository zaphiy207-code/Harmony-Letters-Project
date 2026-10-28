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

    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (
        strlen($username) < 3 ||
        strlen($username) > 50
    ) {
        $error = 'Username must contain 3–50 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must contain at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $check = $pdo->prepare(
                'SELECT user_id FROM users
                 WHERE username = ? OR email = ?'
            );
            $check->execute([$username, $email]);

            if ($check->fetch()) {
                $error = 'That username or email is already registered.';
            } else {
                $hash = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $insert = $pdo->prepare(
                    "INSERT INTO users
                     (username, email, password_hash, role)
                     VALUES (?, ?, ?, 'user')"
                );
                $insert->execute([$username, $email, $hash]);

                header('Location: signin.php?registered=1');
                exit;
            }
        } catch (PDOException $e) {
            error_log('Registration error: ' . $e->getMessage());
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Account | Harmony Letters Project</title>
<link rel="stylesheet" href="harmony.css">
</head>
<body>
<div id="stars"></div>
<main class="auth-page">
    <h1>Create Account</h1>
    <p>Join the Harmony Letters music community.</p>

    <?php if ($error): ?>
        <p role="alert"><?= h($error) ?></p>
    <?php endif; ?>

    <form method="post" action="createacc.php">
        <input type="hidden" name="csrf_token"
               value="<?= h($_SESSION['csrf_token']) ?>">

        <label for="username">Username</label>
        <input id="username" name="username" required
               minlength="3" maxlength="50"
               autocomplete="username">

        <label for="email">Email</label>
        <input id="email" name="email" type="email"
               required autocomplete="email">

        <label for="password">Password</label>
        <input id="password" name="password" type="password"
               required minlength="8"
               autocomplete="new-password">

        <label for="confirm_password">Confirm password</label>
        <input id="confirm_password" name="confirm_password"
               type="password" required minlength="8"
               autocomplete="new-password">

        <button type="submit">Create Account</button>
    </form>

    <p>Already registered? <a href="signin.php">Sign in</a></p>
    <p><a href="index.html">Return Home</a></p>
<script src="js/stars.js"></script>
</main>
</body>
</html>
