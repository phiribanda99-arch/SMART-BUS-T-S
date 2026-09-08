<?php
$options = getopt('', ['email:', 'password::']);
if (!isset($options['email'])) {
    echo "Usage: php create_admin.php --email=admin@example.com --password=Admin@123\n";
    exit(1);
}

$email = $options['email'];
$password = $options['password'] ?? null;

if (!$password) {
    echo "Enter password for {$email}: ";
    $password = trim(fgets(STDIN));
}

require __DIR__ . '/../includes/db_connect.php';

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();

$hash = password_hash($password, PASSWORD_DEFAULT);

if ($user) {
    $update = $pdo->prepare('UPDATE users SET password = :password, role = :role WHERE id = :id');
    $update->execute(['password' => $hash, 'role' => 'admin', 'id' => $user['id']]);
    echo "Updated existing user {$email} as admin.\n";
} else {
    $insert = $pdo->prepare('INSERT INTO users (full_name,email,password,role) VALUES (:full_name,:email,:password,:role)');
    $insert->execute([
        'full_name' => 'Admin User',
        'email' => $email,
        'password' => $hash,
        'role' => 'admin'
    ]);
    echo "Created admin user {$email}.\n";
}

echo "Done. You can now login with {$email}.\n";
