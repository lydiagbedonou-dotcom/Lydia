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
        $confirm   = post_string('confirm');

        if ($rawPseudo === null || $password === null || $confirm === null) {
            $errors[] = 'Données invalides.';
        } else {
            $pseudo = trim($rawPseudo);

            if ($pseudo === '' || $password === '' || $confirm === '') {
                $errors[] = 'Tous les champs sont obligatoires.';
            }
            if ($pseudo !== '' && !preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $pseudo)) {
                $errors[] = 'Le pseudo doit contenir 3 à 30 caractères (lettres, chiffres, _ . -).';
            }
            if ($password !== '' && strlen($password) < 8) {
                $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
            }
            if ($password !== $confirm) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }

            if (!$errors) {
                try {
                    // Disponibilité du pseudonyme (4.2)
                    $stmt = pdo()->prepare('SELECT id FROM users WHERE pseudo = :p LIMIT 1');
                    $stmt->execute([':p' => $pseudo]);
                    if ($stmt->fetch()) {
                        $errors[] = 'Ce pseudo est déjà utilisé.';
                    } else {
                        // Enregistrement (4.3) : mot de passe hashé
                        $stmt = pdo()->prepare(
                            'INSERT INTO users (pseudo, password_hash) VALUES (:p, :h)'
                        );
                        $stmt->execute([
                            ':p' => $pseudo,
                            ':h' => password_hash($password, PASSWORD_DEFAULT),
                        ]);
                        flash_set('success', 'Compte créé avec succès. Vous pouvez vous connecter.');
                        redirect('login.php');
                    }
                } catch (PDOException $ex) {
                    // 1062 = doublon (course critique entre deux inscriptions)
                    $errors[] = ($ex->errorInfo[1] ?? 0) === 1062
                        ? 'Ce pseudo est déjà utilisé.'
                        : "Erreur serveur lors de l'inscription. Veuillez réessayer.";
                }
            }
        }
    }
}

$pageTitle = 'Inscription';
require __DIR__ . '/includes/header.php';
?>
<div class="layout">
    <section class="panel-left auth">
        <form method="post" action="register.php" class="card" novalidate>
            <?= csrf_field() ?>
            <div class="field">
                <label for="pseudo">Pseudo</label>
                <input type="text" id="pseudo" name="pseudo" maxlength="30" value="<?= e($pseudo) ?>" autocomplete="username">
            </div>
            <div class="field">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" autocomplete="new-password">
            </div>
            <div class="field">
                <label for="confirm">Confirmer mot de passe</label>
                <input type="password" id="confirm" name="confirm" autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-block">S'inscrire</button>
            <p class="alt">Avez-vous un compte ? <a href="login.php">Se connecter</a></p>
        </form>

        <?php if ($errors): ?>
            <div class="alert alert-warning">
                <?php foreach ($errors as $msg): ?><div><?= e($msg) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
