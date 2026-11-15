-- Mjestopis - shema baze podataka (MySQL)
-- Izvršavanje briše postojeće tablice i stvara ih ispočetka.
-- Nakon ove skripte treba izvršiti baza/teritorij.sql, a zatim baza/testni_podaci.sql

CREATE DATABASE IF NOT EXISTS mjestopis CHARACTER SET utf8mb4 COLLATE utf8mb4_croatian_ci;

USE mjestopis;

SET NAMES utf8mb4;

-- Tablice se brišu obrnutim redom odnosno prvo one koje pokazuju na druge.
DROP TABLE IF EXISTS korisnici;
DROP TABLE IF EXISTS uloge;
DROP TABLE IF EXISTS naselja;
DROP TABLE IF EXISTS gradovi_opcine;
DROP TABLE IF EXISTS zupanije;

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

-- Tablica zupanije
-- Županije su fiksne (svih 21, uključujući Grad Zagreb) pa se ne uređuju kroz aplikaciju. Podaci su u baza/teritorij.sql
CREATE TABLE zupanije (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    naziv VARCHAR(100) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_zupanije_naziv (naziv)
);

-- Tablica gradovi_opcine
-- Svih 556 gradova i općina (podaci su u baza/teritorij.sql).
-- lat i lng su koordinate sjedišta, koriste se za kartu i prognozu.
-- Ista županija ne može imati dva grada/općine istog naziva.
-- Županija se ne može obrisati dok u njoj postoji neki grad.
CREATE TABLE gradovi_opcine (
    id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    zupanija_id TINYINT UNSIGNED NOT NULL,
    naziv VARCHAR(100) NOT NULL,
    vrsta ENUM('grad', 'opcina') NOT NULL DEFAULT 'grad',
    lat DECIMAL(8,6) NOT NULL,
    lng DECIMAL(9,6) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_gradovi_opcine_zupanija_naziv (zupanija_id, naziv),
    CONSTRAINT fk_gradovi_opcine_zupanija FOREIGN KEY (zupanija_id) REFERENCES zupanije (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT ck_gradovi_opcine_lat CHECK (lat BETWEEN -90 AND 90),
    CONSTRAINT ck_gradovi_opcine_lng CHECK (lng BETWEEN -180 AND 180)
);

-- Tablica naselja
-- Sva naselja iz popisa stanovništva (podaci su u baza/teritorij.sql).
-- Isti grad/općina ne može imati dva naselja istog naziva, a istoimena naselja u različitim općinama su dopuštena.
-- Grad/općina se ne može obrisati dok ima naselja.
CREATE TABLE naselja (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    grad_opcina_id SMALLINT UNSIGNED NOT NULL,
    naziv VARCHAR(150) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_naselja_grad_opcina_naziv (grad_opcina_id, naziv),
    KEY ix_naselja_naziv (naziv),
    CONSTRAINT fk_naselja_grad_opcina FOREIGN KEY (grad_opcina_id) REFERENCES gradovi_opcine (id) ON DELETE RESTRICT ON UPDATE CASCADE
);
