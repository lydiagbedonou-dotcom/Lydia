<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if (is_logged_in()) {
    redirect('index.php');
}

$pseudo = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Session expirée ou requête invalide. Veuillez réessayer.';
    } else {
        $rawPseudo = post_string('pseudo');
        $password  = post_string('password');

        if ($rawPseudo === null || $password === null) {
            $errors[] = 'Données invalides.';
        } else {
            $pseudo = trim($rawPseudo);
            if ($pseudo === '' || $password === '') {
                $errors[] = 'Veuillez renseigner le pseudo et le mot de passe.';
            } else {
                try {
                    $stmt = pdo()->prepare('SELECT id, pseudo, password_hash FROM users WHERE pseudo = :p LIMIT 1');
                    $stmt->execute([':p' => $pseudo]);
                    $user = $stmt->fetch();

                    if ($user && password_verify($password, $user['password_hash'])) {
                        // Ouverture de session (4.5)
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = (int) $user['id'];
                        $_SESSION['pseudo']  = $user['pseudo'];

                        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                            pdo()->prepare('UPDATE users SET password_hash = :h WHERE id = :id')
                                 ->execute([':h' => password_hash($password, PASSWORD_DEFAULT), ':id' => $user['id']]);
                        }
                        redirect('index.php');
                    }
                    $errors[] = 'Pseudo ou mot de passe incorrect.';
                } catch (PDOException $ex) {
                    $errors[] = 'Erreur serveur lors de la connexion. Veuillez réessayer.';
                }
            }
        }
    }
}

$flash = flash_get();
$pageTitle = 'Connexion';
require __DIR__ . '/includes/header.php';
?>
<div class="layout">
    <section class="panel-left auth">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" class="card" novalidate>
            <?= csrf_field() ?>
            <div class="field">
                <label for="pseudo">Pseudo</label>
                <input type="text" id="pseudo" name="pseudo" maxlength="30" value="<?= e($pseudo) ?>" autocomplete="username">
            </div>
            <div class="field">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-block">Se connecter</button>
            <p class="alt">Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
        </form>

        <?php if ($errors): ?>
            <div class="alert alert-warning">
                <?php foreach ($errors as $msg): ?><div><?= e($msg) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
