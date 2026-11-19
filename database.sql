-- ============================================================
-- TP HECM - Application de vérification d'anagrammes
-- Script de création de la base de données
-- ============================================================

CREATE DATABASE IF NOT EXISTS anagramme_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE anagramme_db;

-- Table des utilisateurs (Partie III)
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pseudo        VARCHAR(30)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,          -- hash généré par password_hash()
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_pseudo (pseudo)           -- garantit l'unicité du pseudonyme
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des vérifications (Parties II et III)
CREATE TABLE IF NOT EXISTS verifications (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NULL,                -- NULL si visiteur non connecté
    string1     VARCHAR(100) NOT NULL,            -- première chaîne saisie
    string2     VARCHAR(100) NOT NULL,            -- deuxième chaîne saisie
    is_anagram  TINYINT(1)   NOT NULL,            -- résultat : 1 = anagrammes, 0 = non
    verified_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP, -- date de la vérification
    PRIMARY KEY (id),
    KEY idx_verifications_user (user_id, verified_at),
    CONSTRAINT fk_verifications_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
