<?php
declare(strict_types=1);

require_once __DIR__ . "/../config.php";

/* ---------- Sessions (Partie III - 4.5) ---------- */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        "lifetime" => 0,
        "path"     => "/",
        "httponly" => true,
        "samesite" => "Lax",
        "secure"   => !empty($_SERVER["HTTPS"]),
    ]);
    session_start();
}

/* ---------- Connexion PDO (Partie II - 3.2) ---------- */
function pdo(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf("mysql:host=%s;port=%s;dbname=%s;charset=%s",
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }
    return $pdo;
}

/* ---------- Utilitaires ---------- */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

function redirect(string $url): never
{
    header("Location: " . $url);
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION["user_id"]);
}

/** Récupère une valeur POST uniquement si c est une chaîne (rejette tableaux, etc.). */
function post_string(string $key): ?string
{
    $v = $_POST[$key] ?? null;
    return is_string($v) ? $v : null;
}

/* ---------- Protection CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf"];
}

function csrf_field(): string
{
    return "<input type=\"hidden\" name=\"csrf\" value=\"" . e(csrf_token()) . "\">";
}

function csrf_valid(): bool
{
    $sent = post_string("csrf");
    return $sent !== null && hash_equals($_SESSION["csrf"] ?? "", $sent);
}

/* ---------- Messages flash ---------- */
function flash_set(string $type, string $message): void
{
    $_SESSION["flash"] = ["type" => $type, "message" => $message];
}

function flash_get(): ?array
{
    $f = $_SESSION["flash"] ?? null;
    unset($_SESSION["flash"]);
    return $f;
}
