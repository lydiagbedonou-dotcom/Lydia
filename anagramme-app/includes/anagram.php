<?php
declare(strict_types=1);

/**
 * Logique du vérificateur d anagrammes (Partie I).
 */

const ANAGRAM_MAX_LENGTH = 100;

/**
 * Normalise une chaîne : minuscules, sans espaces, apostrophes ni tirets.
 * Les lettres accentuées sont conservées (é et e sont deux caractères différents).
 */
function anagram_normalize(string $s): string
{
    $s = mb_strtolower(trim($s), "UTF-8");
    return (string) preg_replace("/[\\s\x27’-]+/u", "", $s);
}

/**
 * Valide une chaîne saisie. Retourne un message d erreur, ou null si valide.
 * Cas gérés : champ vide, caractères non autorisés, encodage invalide, trop long.
 */
function anagram_validate(?string $value, string $label): ?string
{
    if ($value === null) {
        return "$label : donnée invalide.";
    }
    if (!mb_check_encoding($value, "UTF-8")) {
        return "$label : encodage invalide.";
    }
    $value = trim($value);
    if ($value === "") {
        return "$label : ce champ est obligatoire.";
    }
    if (mb_strlen($value, "UTF-8") > ANAGRAM_MAX_LENGTH) {
        return "$label : maximum " . ANAGRAM_MAX_LENGTH . " caractères.";
    }
    // Autorisés : lettres (accents inclus), chiffres, espace, apostrophe, tiret
    if (!preg_match("/^[\\p{L}\\p{N}\\s\x27’-]+$/u", $value)) {
        return "$label : caractères non autorisés (lettres, chiffres, espaces, apostrophes et tirets uniquement).";
    }
    if (anagram_normalize($value) === "") {
        return "$label : la saisie doit contenir au moins une lettre ou un chiffre.";
    }
    return null;
}

/**
 * Détermine si deux chaînes sont des anagrammes.
 *
 * Algorithme : comptage des occurrences de chaque caractère.
 *   - Temps   : O(n + m)  (un parcours de chaque chaîne)
 *   - Espace  : O(k)      (k = nombre de caractères distincts)
 */
function are_anagrams(string $a, string $b): bool
{
    $countA = array_count_values(mb_str_split(anagram_normalize($a), 1, "UTF-8"));
    $countB = array_count_values(mb_str_split(anagram_normalize($b), 1, "UTF-8"));

    if (count($countA) !== count($countB)) {
        return false;
    }
    foreach ($countA as $char => $n) {
        if (($countB[$char] ?? 0) !== $n) {
            return false;
        }
    }
    return true;
}
