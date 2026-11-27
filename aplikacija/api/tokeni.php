<?php

// Koliko sati vrijedi token
const API_TOKEN_TRAJANJE_SATI = 24;

// POST /v1/tokeni — prijava: {"email": "…", "lozinka": "…"} daje novi token
function api_token_prijava()
{
    $tijelo = api_tijelo();

    $greske = [];
    foreach (['email', 'lozinka'] as $polje) {
        if (!isset($tijelo[$polje]) || !is_string($tijelo[$polje]) || trim($tijelo[$polje]) === '') {
            $greske[$polje] = 'Obavezno polje (tekst).';
        }
    }
    if (count($greske) > 0) {
        api_greska(400, 'Neispravan unos.', $greske);
    }

    // Ista poruka za nepostojeći email i krivu lozinku, kao na stranici prijave
    $korisnik = korisnik_po_emailu(trim($tijelo['email']));
    if ($korisnik === null || !password_verify($tijelo['lozinka'], $korisnik['lozinka_hash'])) {
        api_greska(401, 'Pogrešan email ili lozinka.');
    }
    if (!$korisnik['aktivan']) {
        api_greska(403, 'Ovaj račun je deaktiviran.');
    }

    tokeni_obrisi_istekle();
    $token = token_dodaj($korisnik['id'], API_TOKEN_TRAJANJE_SATI);
    $zapis = token_vazeci($token);
    $podaci_korisnika = korisnik_po_id($korisnik['id']);

    api_odgovor([
        'token' => $token,
        'vrsta' => 'Bearer',
        'istjece' => api_datum($zapis['istjece']),
        'korisnik' => [
            'id' => $podaci_korisnika['id'],
            'ime' => $podaci_korisnika['ime'],
            'uloga' => $podaci_korisnika['uloga'],
        ],
    ], 201);
}

// DELETE /v1/tokeni — odjava: briše token kojim je poslan zahtjev
function api_token_odjava()
{
    $korisnik = api_zahtijevaj_prijavu();

    token_obrisi($korisnik['token_id']);

    api_bez_sadrzaja();
}
