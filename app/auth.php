<?php
session_start();
require_once __DIR__ . '/db.php';

function is_logged_in() {
    return isset($_SESSION['user']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

function current_user() {
    return $_SESSION['user'] ?? null;
}

function login($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role FROM employees WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        // Support bcrypt or crypt() if DB uses crypt()
        $hash = $user['password_hash'];
        // Try password_verify first (if PHP-side hashed)
        if (password_verify($password, $hash) || crypt($password, $hash) === $hash) {
            // remove sensitive info
            unset($user['password_hash']);
            $_SESSION['user'] = $user;
            return true;
        }
    }
    return false;
}
