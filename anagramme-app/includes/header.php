<?php
/** @var string $pageTitle */
$pageTitle = $pageTitle ?? "Anagramme";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> - Anagramme</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="app">
    <header class="topbar">
        <a class="brand" href="index.php">Anagramme</a>
        <?php if (is_logged_in()): ?>
            <span class="user"><span class="avatar" aria-hidden="true"></span><?= e($_SESSION["pseudo"] ?? "") ?></span>
        <?php else: ?>
            <a class="btn btn-small" href="login.php">Se connecter</a>
        <?php endif; ?>
    </header>
    <main class="content">
