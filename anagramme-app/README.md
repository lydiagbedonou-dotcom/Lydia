# TP HECM – Application web de vérification d'anagrammes (PHP / MySQL / PDO)

Application développée avec **HTML5, CSS3, PHP 8.x, MySQL et PDO**.

## Fonctionnalités

**Partie I – Vérificateur**
- Formulaire avec les champs *String 1*, *String 2* et le bouton *Soumettre*
- Algorithme de comptage des occurrences (insensible à la casse, espaces/apostrophes/tirets ignorés)
- Bordure **verte** si anagrammes, **rouge** sinon
- Gestion des erreurs : champs vides, caractères non autorisés, données invalides (tableaux, encodage, longueur > 100)

**Partie II – Historique**
- Table `verifications` : chaîne 1, chaîne 2, résultat, date
- Connexion via PDO (requêtes préparées)
- Tableau de l'historique (lignes vertes / rouges)

**Partie III – Utilisateurs**
- Inscription (pseudo, mot de passe, confirmation), vérification de la disponibilité du pseudo
- Mots de passe stockés **hashés** (`password_hash` / `password_verify`)
- Connexion, sessions PHP, déconnexion (met fin à la session uniquement)
- Chaque vérification d'un utilisateur connecté est liée à son compte ; l'historique affiché lui est propre

## Prérequis
- PHP 8.0 ou supérieur (extensions `pdo_mysql` et `mbstring`)
- MySQL 5.7+ / MariaDB 10.3+
- Un serveur local : XAMPP, WAMP, Laragon ou MAMP (ou le serveur intégré de PHP)

## Installation

1. **Copier** le dossier `anagramme-app` dans le répertoire web :
   - XAMPP : `C:\xampp\htdocs\anagramme-app`
   - WAMP : `C:\wamp64\www\anagramme-app`
2. **Démarrer** Apache et MySQL.
3. **Créer la base** : ouvrir phpMyAdmin (`http://localhost/phpmyadmin`), onglet *Importer*, choisir `database.sql`, puis *Exécuter*.
   En ligne de commande : `mysql -u root -p < database.sql`
4. **Configurer** la connexion dans `config.php` (hôte, port, utilisateur, mot de passe) si besoin.
5. **Lancer** : `http://localhost/anagramme-app/`

*Alternative sans Apache :* dans le dossier du projet, exécuter `php -S localhost:8000` puis ouvrir `http://localhost:8000`.

## Scénario de test
1. Sans compte : `anagram` / `nagaram` → bordure verte ; `rat` / `car` → bordure rouge.
2. Soumettre avec un champ vide, ou avec `abc$%` → message d'erreur.
3. *S'inscrire* : pseudo, mot de passe (8 caractères min.), confirmation. Tester un pseudo déjà pris et des mots de passe différents.
4. *Se connecter*, effectuer des vérifications : elles apparaissent dans le tableau d'historique.
5. Se déconnecter (icône en bas à gauche), se reconnecter : l'historique est conservé.
6. Créer un second compte : son historique est distinct.

## Structure
```
anagramme-app/
├── index.php          Vérificateur + historique
├── register.php       Inscription
├── login.php          Connexion
├── logout.php         Déconnexion
├── config.php         Paramètres MySQL
├── database.sql       Script de création de la base
├── includes/
│   ├── bootstrap.php  Session, PDO, CSRF, utilitaires
│   ├── anagram.php    Validation + algorithme
│   ├── header.php
│   └── footer.php
└── assets/style.css
```

## Complexité de l'algorithme
Comptage des occurrences : temps **O(n + m)**, espace **O(k)** (k = caractères distincts).

## Sécurité
Requêtes préparées PDO, échappement HTML (`htmlspecialchars`), jeton CSRF, mots de passe hashés, régénération de l'ID de session à la connexion, cookies de session `HttpOnly`.

## Captures d'écran à fournir (livrable 3)
À prendre vous-même sur votre installation : écran initial, résultat positif, résultat négatif, erreurs de validation, inscription, connexion, historique.
