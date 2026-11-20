-- Mjestopis - shema baze podataka (MySQL)
-- Izvršavanje briše postojeće tablice i stvara ih ispočetka.
-- Nakon ove skripte treba izvršiti baza/teritorij.sql, a zatim baza/testni_podaci.sql

CREATE DATABASE IF NOT EXISTS mjestopis CHARACTER SET utf8mb4 COLLATE utf8mb4_croatian_ci;

USE mjestopis;

SET NAMES utf8mb4;

-- Tablice se brišu obrnutim redom odnosno prvo one koje pokazuju na druge.
DROP TABLE IF EXISTS fotografije_lokacija;
DROP TABLE IF EXISTS lokacije;
DROP TABLE IF EXISTS vrste_lokacija;
DROP TABLE IF EXISTS vijesti;
DROP TABLE IF EXISTS slike;
DROP TABLE IF EXISTS kategorije;
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

-- Tablica kategorije
-- Kategorije vijesti. Kategorija se ne može obrisati dok je koristi neka vijest.
CREATE TABLE kategorije (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    naziv VARCHAR(60) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_kategorije_naziv (naziv)
);

-- Tablica slike
-- Sama datoteka je u mapi prijenosi/ pod nasumičnim nazivom (datoteka), a izvorni naziv s korisnikovog računala sprema se samo kao podatak.
-- Korisnik koji je prenio sliku se ne briše nego deaktivira pa slika ostaje.
CREATE TABLE slike (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    datoteka VARCHAR(50) NOT NULL,
    izvorni_naziv VARCHAR(255) NOT NULL,
    vrsta ENUM('image/jpeg', 'image/png', 'image/webp') NOT NULL,
    velicina INT UNSIGNED NOT NULL,
    sirina SMALLINT UNSIGNED NOT NULL,
    visina SMALLINT UNSIGNED NOT NULL,
    prenio_id INT UNSIGNED NOT NULL,
    kreirano DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_slike_datoteka (datoteka),
    KEY ix_slike_prenio (prenio_id),
    CONSTRAINT fk_slike_prenio FOREIGN KEY (prenio_id) REFERENCES korisnici (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT ck_slike_velicina CHECK (velicina > 0),
    CONSTRAINT ck_slike_dimenzije CHECK (sirina > 0 AND visina > 0)
);

-- Tablica vijesti
-- grad_opcina_id NULL znači da je vijest za cijelu Hrvatsku.
-- Javno se prikazuju samo vijesti sa statusom 'objavljeno' kojima je objavljeno_u već prošlo.
-- Nacrt nema datum objave. Autor se ne briše nego deaktivira pa vijest ostaje.
-- slika_id NULL znači da vijest nema svoju sliku. Brisanjem slike vijest ostaje bez nje.
CREATE TABLE vijesti (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    kategorija_id TINYINT UNSIGNED NOT NULL,
    grad_opcina_id SMALLINT UNSIGNED NULL,
    autor_id INT UNSIGNED NOT NULL,
    slika_id INT UNSIGNED NULL,
    naslov VARCHAR(200) NOT NULL,
    sazetak VARCHAR(400) NOT NULL,
    sadrzaj MEDIUMTEXT NOT NULL,
    status ENUM('nacrt', 'objavljeno', 'povuceno') NOT NULL DEFAULT 'nacrt',
    objavljeno_u DATETIME NULL,
    kreirano DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    azurirano DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_vijesti_objava (status, objavljeno_u),
    KEY ix_vijesti_kategorija (kategorija_id),
    KEY ix_vijesti_grad_opcina (grad_opcina_id),
    KEY ix_vijesti_autor (autor_id),
    KEY ix_vijesti_slika (slika_id),
    CONSTRAINT fk_vijesti_kategorija FOREIGN KEY (kategorija_id) REFERENCES kategorije (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_vijesti_grad_opcina FOREIGN KEY (grad_opcina_id) REFERENCES gradovi_opcine (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_vijesti_autor FOREIGN KEY (autor_id) REFERENCES korisnici (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_vijesti_slika FOREIGN KEY (slika_id) REFERENCES slike (id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT ck_vijesti_objava CHECK (status = 'nacrt' OR objavljeno_u IS NOT NULL)
);

-- Tablica vrste_lokacija
-- Vrste lokacija (znamenitost, park, plaža…) uređuje administrator. Vrsta se ne može obrisati dok je koristi neka lokacija.
CREATE TABLE vrste_lokacija (
    id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    naziv VARCHAR(60) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_vrste_lokacija_naziv (naziv)
);

-- Tablica lokacije
-- Javno se prikazuju samo objavljene lokacije (objavljeno = 1). Nova lokacija je skrivena dok je urednik ne objavi.
-- Isti grad/općina ne može imati dvije lokacije istog naziva.
-- lat i lng su koordinate same lokacije (za kartu).
-- radno_vrijeme je JSON po danima, npr. {"pon": ["09:00", "17:00"], …, "ned": null, "napomena": "Ljeti do 20 h"}, gdje null znači zatvoreno.
-- radno_vrijeme NULL znači da lokacija nema radno vrijeme (uvijek otvoreno, npr. plaža ili vidikovac).
-- Autor se ne briše nego deaktivira pa lokacija ostaje.
CREATE TABLE lokacije (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    vrsta_id TINYINT UNSIGNED NOT NULL,
    grad_opcina_id SMALLINT UNSIGNED NOT NULL,
    autor_id INT UNSIGNED NOT NULL,
    naziv VARCHAR(150) NOT NULL,
    opis TEXT NOT NULL,
    adresa VARCHAR(200) NULL,
    lat DECIMAL(8,6) NOT NULL,
    lng DECIMAL(9,6) NOT NULL,
    radno_vrijeme JSON NULL,
    objavljeno TINYINT(1) NOT NULL DEFAULT 0,
    istaknuto TINYINT(1) NOT NULL DEFAULT 0,
    kreirano DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    azurirano DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_lokacije_grad_opcina_naziv (grad_opcina_id, naziv),
    KEY ix_lokacije_objavljeno (objavljeno, istaknuto),
    KEY ix_lokacije_vrsta (vrsta_id),
    KEY ix_lokacije_autor (autor_id),
    CONSTRAINT fk_lokacije_vrsta FOREIGN KEY (vrsta_id) REFERENCES vrste_lokacija (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_lokacije_grad_opcina FOREIGN KEY (grad_opcina_id) REFERENCES gradovi_opcine (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_lokacije_autor FOREIGN KEY (autor_id) REFERENCES korisnici (id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT ck_lokacije_lat CHECK (lat BETWEEN -90 AND 90),
    CONSTRAINT ck_lokacije_lng CHECK (lng BETWEEN -180 AND 180),
    CONSTRAINT ck_lokacije_objavljeno CHECK (objavljeno IN (0, 1)),
    CONSTRAINT ck_lokacije_istaknuto CHECK (istaknuto IN (0, 1))
);

-- Tablica fotografije_lokacija
-- Lokacija ima najviše 5 fotografija (provjerava aplikacija). Svaka slika pripada samo jednoj lokaciji.
-- redoslijed određuje poredak u galeriji, a prva fotografija prikazuje se na kartici lokacije.
-- Brisanjem lokacije ili slike briše se i veza između njih (datoteke slika briše aplikacija).
CREATE TABLE fotografije_lokacija (
    lokacija_id INT UNSIGNED NOT NULL,
    slika_id INT UNSIGNED NOT NULL,
    redoslijed TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY (lokacija_id, slika_id),
    UNIQUE KEY uq_fotografije_lokacija_slika (slika_id),
    CONSTRAINT fk_fotografije_lokacija_lokacija FOREIGN KEY (lokacija_id) REFERENCES lokacije (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_fotografije_lokacija_slika FOREIGN KEY (slika_id) REFERENCES slike (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT ck_fotografije_lokacija_redoslijed CHECK (redoslijed BETWEEN 1 AND 5)
);
