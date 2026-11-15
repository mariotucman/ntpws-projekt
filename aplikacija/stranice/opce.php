<?php
// Opće stranice: početna i stranica za nepostojeću adresu.

function stranica_pocetna()
{
    $redak = db_jedan_redak('SELECT COUNT(*) AS broj FROM korisnici'); // test

    prikazi('pocetna', [
        'broj_korisnika' => $redak['broj'],
    ]);
}

function stranica_nije_pronadena()
{
    http_response_code(404);

    prikazi('greska-404');
}

function stranica_zabranjeno($razlog = 'Obrazac je istekao ili nije poslan s ove stranice. Vrati se, osvježi stranicu i pokušaj ponovno.')
{
    http_response_code(403);

    prikazi('greska-403', [
        'razlog' => $razlog,
    ]);
}
