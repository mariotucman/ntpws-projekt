<?php

require __DIR__ . '/../aplikacija/greske.php';
require __DIR__ . '/../aplikacija/konfiguracija.php';
require __DIR__ . '/../aplikacija/baza.php';
require __DIR__ . '/../aplikacija/prikaz.php';
require __DIR__ . '/../aplikacija/stranice/opce.php';
require __DIR__ . '/../aplikacija/stranice/korisnici.php';

// Popis dopuštenih stranica
$stranice = [
    'pocetna' => 'stranica_pocetna',
    'registracija' => 'stranica_registracija',
];

$stranica = $_GET['stranica'] ?? 'pocetna';

if (is_string($stranica) && isset($stranice[$stranica])) {
    $stranice[$stranica]();
} else {
    stranica_nije_pronadena(); // 404 error
}
