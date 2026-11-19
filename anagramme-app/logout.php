<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

// Déconnexion (4.6) : met fin à la session uniquement.
// Le compte et l'historique de l'utilisateur restent en base de données.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid()) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
redirect('index.php');
