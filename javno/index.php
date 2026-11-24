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
require __DIR__ . '/../aplikacija/podaci/lokacije.php';
require __DIR__ . '/../aplikacija/podaci/recenzije.php';
require __DIR__ . '/../aplikacija/podaci/dogadanja.php';
require __DIR__ . '/../aplikacija/podaci/planovi.php';
require __DIR__ . '/../aplikacija/servisi/prognoza.php';
require __DIR__ . '/../aplikacija/autentifikacija.php';
require __DIR__ . '/../aplikacija/sigurnost.php';
require __DIR__ . '/../aplikacija/stranice/opce.php';
require __DIR__ . '/../aplikacija/stranice/korisnici.php';
require __DIR__ . '/../aplikacija/stranice/vijesti.php';
require __DIR__ . '/../aplikacija/stranice/lokacije.php';
require __DIR__ . '/../aplikacija/stranice/dogadanja.php';
require __DIR__ . '/../aplikacija/stranice/planovi.php';
require __DIR__ . '/../aplikacija/stranice/administracija.php';
require __DIR__ . '/../aplikacija/stranice/administracija-lokacija.php';
require __DIR__ . '/../aplikacija/stranice/administracija-dogadanja.php';

// Popis dopuštenih stranica
$stranice = [
    'pocetna' => 'stranica_pocetna',
    'registracija' => 'stranica_registracija',
    'prijava' => 'stranica_prijava',
    'odjava' => 'stranica_odjava',
    'profil' => 'stranica_profil',
    'lozinka' => 'stranica_lozinka',
    'lokacije' => 'stranica_lokacije',
    'lokacija' => 'stranica_lokacija',
    'recenzija-obrisi' => 'stranica_recenzija_obrisi',
    'dogadanja' => 'stranica_dogadanja',
    'dogadanje' => 'stranica_dogadanje',
    'moj-plan' => 'stranica_moj_plan',
    'plan' => 'stranica_plan',
    'plan-uredi' => 'stranica_plan_uredi',
    'plan-obrisi' => 'stranica_plan_obrisi',
    'plan-dodaj' => 'stranica_plan_dodaj',
    'vijesti' => 'stranica_vijesti',
    'vijest' => 'stranica_vijest',
    'slika' => 'stranica_slika',
    'admin-vijesti' => 'stranica_admin_vijesti',
    'admin-vijest' => 'stranica_admin_vijest',
    'admin-vijest-obrisi' => 'stranica_admin_vijest_obrisi',
    'admin-kategorije' => 'stranica_admin_kategorije',
    'admin-korisnici' => 'stranica_admin_korisnici',
    'admin-korisnik' => 'stranica_admin_korisnik',
    'admin-kategorija' => 'stranica_admin_kategorija',
    'admin-kategorija-obrisi' => 'stranica_admin_kategorija_obrisi',
    'admin-lokacije' => 'stranica_admin_lokacije',
    'admin-lokacija' => 'stranica_admin_lokacija',
    'admin-lokacija-obrisi' => 'stranica_admin_lokacija_obrisi',
    'admin-recenzije' => 'stranica_admin_recenzije',
    'admin-recenzija-skrivenost' => 'stranica_admin_recenzija_skrivenost',
    'admin-dogadanja' => 'stranica_admin_dogadanja',
    'admin-dogadanje' => 'stranica_admin_dogadanje',
    'admin-dogadanje-obrisi' => 'stranica_admin_dogadanje_obrisi',
    'admin-vrste-dogadanja' => 'stranica_admin_vrste_dogadanja',
    'admin-vrsta-dogadanja' => 'stranica_admin_vrsta_dogadanja',
    'admin-vrsta-dogadanja-obrisi' => 'stranica_admin_vrsta_dogadanja_obrisi',
    'admin-vrste-lokacija' => 'stranica_admin_vrste_lokacija',
    'admin-vrsta-lokacije' => 'stranica_admin_vrsta_lokacije',
    'admin-vrsta-lokacije-obrisi' => 'stranica_admin_vrsta_lokacije_obrisi',
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
