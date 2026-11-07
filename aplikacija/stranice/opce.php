<?php
// Opće stranice: početna i stranica za nepostojeću adresu.

function stranica_pocetna()
{
    $redak = db_jedan_redak('SELECT COUNT(*) AS broj FROM korisnici'); // test

    echo '<!doctype html><html lang="hr"><head><meta charset="utf-8"><title>Mjestopis</title></head>';
    echo '<body><h1>Mjestopis</h1><p>Dobrodošli na Mjestopis.</p>';
    echo '<p>Broj korisnika u bazi: ' . $redak['broj'] . '</p></body></html>';
}

function stranica_nije_pronadena()
{
    http_response_code(404);
    echo '<!doctype html><html lang="hr"><head><meta charset="utf-8"><title>Stranica nije pronađena</title></head>';
    echo '<body><h1>Stranica nije pronađena</h1><p><a href="index.php">Povratak na početnu</a></p></body></html>';
}
