-- Mjestopis - shema baze podataka (MySQL)
-- Izvršavanje briše postojeće tablice i stvara ih ispočetka.
-- Nakon ove skripte treba izvršiti i datoteku baza/testni_podaci.sql

CREATE DATABASE IF NOT EXISTS mjestopis CHARACTER SET utf8mb4 COLLATE utf8mb4_croatian_ci;

USE mjestopis;

SET NAMES utf8mb4;

-- Tablice se brišu obrnutim redom odnosno prvo one koje pokazuju na druge.
DROP TABLE IF EXISTS korisnici;
DROP TABLE IF EXISTS uloge;

-- Tablica uloge
-- Posjetitelj nema ulogu jer je to samo neprijavljeni korisnik.
CREATE TABLE uloge (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    oznaka VARCHAR(30) NOT NULL,
    naziv VARCHAR(50) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_uloge_oznaka (oznaka)
);

-- Tablica korisnici
-- Email je jedinstven (neosjetljiv na velika i mala slova npr. Ivan@test.hr i ivan@test.hr su isti računi).
-- Uloga se ne može obrisati dok je koristi neki korisnik.
CREATE TABLE korisnici (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    uloga_id TINYINT UNSIGNED NOT NULL,
    ime VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL,
    lozinka_hash VARCHAR(255) NOT NULL,
    aktivan TINYINT(1) NOT NULL DEFAULT 1,
    kreirano DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    azurirano DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_korisnici_email (email),
    KEY ix_korisnici_uloga (uloga_id),
    CONSTRAINT fk_korisnici_uloga FOREIGN KEY (uloga_id) REFERENCES uloge (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT ck_korisnici_aktivan CHECK (aktivan IN (0, 1))
);

-- Osnovni podaci o ulogama bez kojih aplikacija ne radi
INSERT INTO uloge (id, oznaka, naziv) VALUES
(1, 'korisnik', 'Registrirani korisnik'),
(2, 'urednik', 'Urednik'),
(3, 'administrator', 'Administrator');
