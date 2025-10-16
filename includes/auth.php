<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

function is_logged_in() {
    return !empty($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: /jogo3/login.php');
        exit;
    }
}

function current_user() {
    if (!is_logged_in()) return null;
    // Fetch latest user data (including avatar)
    global $pdo;
    try {
        $stmt = $pdo->prepare('SELECT id, nome, avatar FROM usuarios WHERE id = :id LIMIT 1');
        $stmt->execute([':id' => $_SESSION['user_id']]);
        $u = $stmt->fetch();
        if ($u) {
            return ['id' => $u['id'], 'nome' => $u['nome'], 'avatar' => $u['avatar']];
        }
    } catch (Exception $e) {
        // fallback to session values
    }
    return ['id' => $_SESSION['user_id'], 'nome' => $_SESSION['user_nome']];
}
