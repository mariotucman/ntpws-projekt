<?php
// Primjer konfiguracije.
// Treba kopirati ovu datoteku u konfiguracija.php i upisati stvarne podatke.
// Datoteka konfiguracija.php se ne sprema u git jer sadrži lozinke.

define('BAZA_POSLUZITELJ', 'localhost');
define('BAZA_PORT', 3306);
define('BAZA_NAZIV', 'mjestopis');
define('BAZA_KORISNIK', 'root');
define('BAZA_LOZINKA', '');

// Prognoza (OpenMeteo): 'stvarno' je pravi API. Testni načini za demonstraciju kvara servisa:
// 'greska' glumi servis koji ne odgovara, a 'neispravno' daje odgovor bez očekivanih polja (testni-podaci/openmeteo/neispravno.json).
define('PROGNOZA_NACIN', 'stvarno');

// Javna instalacija: adresa s https:// (npr. 'https://mjestopis.hr'). Tada se svaki zahtjev preko HTTP-a preusmjerava na tu adresu, a preglednik pamti da stranicu otvara samo preko HTTPS-a.
// Lokalno ostaje prazno.
define('HTTPS_ADRESA', '');
