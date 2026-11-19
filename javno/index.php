<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../aplikacija/greske.php';
require __DIR__ . '/../aplikacija/konfiguracija.php';
require __DIR__ . '/../aplikacija/baza.php';
require __DIR__ . '/../aplikacija/prikaz.php';
require __DIR__ . '/../aplikacija/podaci/korisnici.php';
require __DIR__ . '/../aplikacija/podaci/podrucja.php';
require __DIR__ . '/../aplikacija/podaci/kategorije.php';
require __DIR__ . '/../aplikacija/podaci/vijesti.php';
require __DIR__ . '/../aplikacija/podaci/slike.php';
require __DIR__ . '/../aplikacija/autentifikacija.php';
require __DIR__ . '/../aplikacija/sigurnost.php';
require __DIR__ . '/../aplikacija/stranice/opce.php';
require __DIR__ . '/../aplikacija/stranice/korisnici.php';
require __DIR__ . '/../aplikacija/stranice/vijesti.php';
require __DIR__ . '/../aplikacija/stranice/administracija.php';

// Popis dopuštenih stranica
$stranice = [
    'pocetna' => 'stranica_pocetna',
    'registracija' => 'stranica_registracija',
    'prijava' => 'stranica_prijava',
    'odjava' => 'stranica_odjava',
    'profil' => 'stranica_profil',
    'lozinka' => 'stranica_lozinka',
    'vijesti' => 'stranica_vijesti',
    'vijest' => 'stranica_vijest',
    'slika' => 'stranica_slika',
    'admin-vijesti' => 'stranica_admin_vijesti',
    'admin-vijest' => 'stranica_admin_vijest',
    'admin-vijest-obrisi' => 'stranica_admin_vijest_obrisi',
    'admin-kategorije' => 'stranica_admin_kategorije',
    'admin-korisnici' => 'stranica_admin_korisnici',
    'admin-kategorija' => 'stranica_admin_kategorija',
    'admin-kategorija-obrisi' => 'stranica_admin_kategorija_obrisi',
];

$stranica = $_GET['stranica'] ?? 'pocetna';

// Prijavljeni korisnik se provjerava prije svake stranice, a deaktivirani se odjavljuje
trenutni_korisnik();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_ispravan()) {
    stranica_zabranjeno(); // 403 error
} elseif (is_string($stranica) && isset($stranice[$stranica])) {
    $stranice[$stranica]();
} else {
    stranica_nije_pronadena(); // 404 error
}
