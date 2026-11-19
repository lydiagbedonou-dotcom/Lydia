<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/anagram.php';

$string1 = '';
$string2 = '';
$errors  = [];      // erreurs de validation (Partie I - 2.5)
$result  = null;    // null = pas de vérification, true = anagrammes, false = non
$dbError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) {
        $errors[] = 'Session expirée ou requête invalide. Veuillez réessayer.';
    } else {
        $raw1 = post_string('string1');
        $raw2 = post_string('string2');
        $string1 = trim($raw1 ?? '');
        $string2 = trim($raw2 ?? '');

        $e1 = anagram_validate($raw1, 'String 1');
        $e2 = anagram_validate($raw2, 'String 2');
        if ($e1) { $errors['string1'] = $e1; }
        if ($e2) { $errors['string2'] = $e2; }

        if (!$errors) {
            $result = are_anagrams($string1, $string2);

            // Enregistrement de la vérification (Partie II - 3.3 / Partie III - 4.7)
            try {
                $stmt = pdo()->prepare(
                    'INSERT INTO verifications (user_id, string1, string2, is_anagram, verified_at)
                     VALUES (:user_id, :s1, :s2, :res, NOW())'
                );
                $stmt->execute([
                    ':user_id' => is_logged_in() ? (int) $_SESSION['user_id'] : null,
                    ':s1'      => $string1,
                    ':s2'      => $string2,
                    ':res'     => $result ? 1 : 0,
                ]);
            } catch (PDOException $ex) {
                $dbError = "La vérification a été effectuée mais n'a pas pu être enregistrée.";
            }
        }
    }
}

// Historique personnel (Partie II - 3.4 / Partie III - 4.7)
$history = [];
if (is_logged_in()) {
    try {
        $stmt = pdo()->prepare(
            'SELECT string1, string2, is_anagram, verified_at
             FROM verifications
             WHERE user_id = :uid
             ORDER BY verified_at DESC, id DESC
             LIMIT 100'
        );
        $stmt->execute([':uid' => (int) $_SESSION['user_id']]);
        $history = $stmt->fetchAll();
    } catch (PDOException $ex) {
        $dbError = $dbError ?? "Impossible de charger l'historique.";
    }
}

$stateClass = $result === null ? '' : ($result ? 'is-success' : 'is-error');
$fieldClass = static function (string $name) use ($errors, $stateClass): string {
    return isset($errors[$name]) ? 'is-invalid' : $stateClass;
};

$flash = flash_get();
$pageTitle = 'Vérificateur';
require __DIR__ . '/includes/header.php';
?>

<div class="layout <?= is_logged_in() ? 'with-history' : '' ?>">

    <section class="panel-left">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <form method="post" action="index.php" class="card <?= $stateClass ?>" novalidate>
            <?= csrf_field() ?>
            <div class="row">
                <div class="field">
                    <label for="string1">String 1</label>
                    <input type="text" id="string1" name="string1" maxlength="<?= ANAGRAM_MAX_LENGTH ?>"
                           value="<?= e($string1) ?>" class="<?= $fieldClass('string1') ?>">
                </div>
                <div class="field">
                    <label for="string2">String 2</label>
                    <input type="text" id="string2" name="string2" maxlength="<?= ANAGRAM_MAX_LENGTH ?>"
                           value="<?= e($string2) ?>" class="<?= $fieldClass('string2') ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-block">Soumettre</button>
        </form>

        <?php if ($errors): ?>
            <div class="alert alert-warning">
                <?php foreach ($errors as $msg): ?><div><?= e($msg) ?></div><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($result !== null): ?>
            <p class="result <?= $result ? 'text-success' : 'text-error' ?>">
                <?= $result ? 'Ce sont des anagrammes.' : 'Ce ne sont pas des anagrammes.' ?>
            </p>
        <?php endif; ?>

        <?php if ($dbError): ?>
            <div class="alert alert-warning"><?= e($dbError) ?></div>
        <?php endif; ?>
    </section>

    <?php if (is_logged_in()): ?>
    <section class="panel-right">
        <h2>Historique</h2>
        <div class="table-wrap">
            <table class="history">
                <thead>
                    <tr><th></th><th>String 1</th><th>String 2</th><th>Résultat</th><th>Date</th></tr>
                </thead>
                <tbody>
                <?php if (!$history): ?>
                    <tr><td colspan="5" class="empty">Aucune vérification pour le moment.</td></tr>
                <?php else: ?>
                    <?php foreach ($history as $i => $row):
                        $cls = $row['is_anagram'] ? 'text-success' : 'text-error'; ?>
                        <tr>
                            <td class="idx <?= $cls ?>"><?= $i ?></td>
                            <td class="<?= $cls ?>"><?= e($row['string1']) ?></td>
                            <td class="<?= $cls ?>"><?= e($row['string2']) ?></td>
                            <td class="<?= $cls ?>"><?= $row['is_anagram'] ? 'Anagrammes' : 'Différents' ?></td>
                            <td class="date"><?= e(date('d/m/Y H:i', strtotime($row['verified_at']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
