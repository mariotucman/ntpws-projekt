<?php
// Opće stranice: početna i stranica za nepostojeću adresu.

function stranica_pocetna()
{
    $redak = db_jedan_redak('SELECT COUNT(*) AS broj FROM korisnici'); // test

    prikazi('pocetna', [
        'naslov' => 'Početna',
        'broj_korisnika' => $redak['broj'],
    ]);
}

function stranica_nije_pronadena()
{
    http_response_code(404);

    prikazi('greska-404', [
        'naslov' => 'Stranica nije pronađena',
    ]);
}
