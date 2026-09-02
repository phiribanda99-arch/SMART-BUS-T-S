<?php
session_start();
require __DIR__ . '/includes/db_connect.php';

$notice = '';
$noticeType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $notice = 'Please enter both email and password.';
            $noticeType = 'error';
        } else {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && $user['password'] && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'] ?: 'Passenger';
                $_SESSION['user_role'] = $user['role'];

                header('Location: ' . ($user['role'] === 'passenger' ? 'passenger-dashboard.php' : 'dashboard.php'));
                exit;
            }

            $notice = 'Invalid email or password.';
            $noticeType = 'error';
        }
    }

    if ($action === 'signup') {
        $fullName = trim($_POST['signupName'] ?? '');
        $email = strtolower(trim($_POST['signupEmail'] ?? ''));
        $password = $_POST['signupPassword'] ?? '';

        if ($fullName === '' || $email === '' || $password === '') {
            $notice = 'Please complete all signup fields.';
            $noticeType = 'error';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $notice = 'Please enter a valid email address.';
            $noticeType = 'error';
        } else {
            $check = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
            $check->execute(['email' => $email]);

            if ($check->fetch()) {
                $notice = 'An account with that email already exists.';
                $noticeType = 'error';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password, role) VALUES (:full_name, :email, :password, :role)');
                $stmt->execute([
                    'full_name' => $fullName,
                    'email' => $email,
                    'password' => $hash,
                    'role' => 'passenger'
                ]);

                $userId = (int) $pdo->lastInsertId();
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $fullName;
                $_SESSION['user_role'] = 'passenger';

                header('Location: passenger-dashboard.php');
                exit;
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Smart Bus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-shell">
        <div class="login-card">
            <h1>Smart Bus</h1>
            <p class="subtitle">Transport Management System</p>

            <?php if ($notice): ?>
                <div class="alert <?php echo $noticeType === 'success' ? 'alert-success' : 'alert-error'; ?>"><?php echo htmlspecialchars($notice); ?></div>
            <?php endif; ?>

            <form method="post" action="index.php">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" placeholder="admin@example.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" placeholder="Enter password" required>
                </div>

                <button type="submit">Login</button>
            </form>

            <div class="divider">OR</div>

            <form method="post" action="index.php">
                <input type="hidden" name="action" value="signup">
                <div class="form-group">
                    <label for="signupName">Full Name</label>
                    <input id="signupName" name="signupName" type="text" placeholder="John Doe" required>
                </div>

                <div class="form-group">
                    <label for="signupEmail">Email</label>
                    <input id="signupEmail" name="signupEmail" type="email" placeholder="newuser@example.com" required>
                </div>

                <div class="form-group">
                    <label for="signupPassword">Password</label>
                    <input id="signupPassword" name="signupPassword" type="password" placeholder="Create password" required>
                </div>

                <button type="submit" class="btn-secondary">Create Account</button>
            </form>

            <div style="margin-top: 14px; text-align: center; color: #4b5563; font-size: 14px;">
                Demo admin: admin@example.com / Admin@123
            </div>
        </div>
    </div>
</body>
</html>
