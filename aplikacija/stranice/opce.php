<?php
// Opće stranice: početna, slika i stranice za greške.

// Početna sa „Što te zanima?”: vrste lokacija vode na popis lokacija te vrste
function stranica_pocetna()
{
    prikazi('pocetna', [
        'vrste' => vrste_lokacija_s_javnim_lokacijama(),
    ]);
}

function stranica_nije_pronadena()
{
    http_response_code(404);

    prikazi('greska', [
        'naslov' => 'Stranica nije pronađena',
        'opis' => 'Stranica koju tražite ne postoji ili je uklonjena.',
    ]);
}

function stranica_zabranjeno($razlog = 'Obrazac je istekao ili nije poslan s ove stranice. Vrati se, osvježi stranicu i pokušaj ponovno.')
{
    http_response_code(403);

    prikazi('greska', [
        'naslov' => 'Zahtjev je odbijen',
        'opis' => $razlog,
    ]);
}

// Šalje sliku iz mape prijenosi/ s vrstom iz baze.
// Posjetitelj vidi samo slike objavljenih vijesti, a urednik i administrator sve slike.
function stranica_slika()
{
    $slika = slika_po_id(get_broj('id'));
    $putanja = $slika !== null ? MAPA_PRIJENOSA . '/' . basename($slika['datoteka']) : '';

    $dopustena = $slika !== null && (ima_ulogu(['administrator', 'urednik']) || slika_javna($slika['id']));

    // Nedopuštene i nepostojeće slike daju 404 error
    if (!$dopustena || !is_file($putanja)) {
        stranica_nije_pronadena();
        return;
    }

    // Preglednik pamti sliku jedan sat da je ne dohvaća svaki put iznova Dulje ne, jer vijest u međuvremenu može biti povučena.
    header_remove('Pragma');
    header_remove('Expires');
    header('Cache-Control: private, max-age=3600');
    header('Content-Type: ' . $slika['vrsta']);
    header('Content-Length: ' . filesize($putanja));
    // Preglednik ne smije pogađati vrstu datoteke, nego koristi samo Content-Type
    header('X-Content-Type-Options: nosniff');

    readfile($putanja);
}
