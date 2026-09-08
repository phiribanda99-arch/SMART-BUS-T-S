<?php

function dbConnect(): PDO
{
    $config = require __DIR__ . '/../config/config.php';
    $db = $config['db'];
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $db['host'],
        $db['port'],
        $db['dbname'],
        $db['charset']
    );

    return new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']);
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect('/index.php');
    }
}

function currentUser(): ?array
{
    if (!isLoggedIn()) {
        return null;
    }

    $pdo = dbConnect();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}

function requireRole(string $role): void
{
    if (!isLoggedIn()) {
        redirect('index.php');
    }

    $user = currentUser();
    if (!$user || $user['role'] !== $role) {
        redirect('index.php');
    }
}

function setFlash(string $message, string $type = 'success'): void
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

function getFlash(): ?array
{
    if (empty($_SESSION['flash_message'])) {
        return null;
    }

    $message = $_SESSION['flash_message'];
    $type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);

    return ['message' => $message, 'type' => $type];
}

function renderFlash(): void
{
    $flash = getFlash();
    if (!$flash) {
        return;
    }

    $class = $flash['type'] === 'error' ? 'alert-danger' : 'alert-success';
    echo '<div class="alert ' . $class . ' mt-3">' . e($flash['message']) . '</div>';
}
