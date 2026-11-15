<?php

// Provjerava ime i email (registracija i profil)
function provjeri_ime_i_email($ime, $email, $osim_id = 0)
{
    $greske = [];

    if ($ime === '') {
        $greske['ime'] = 'Upiši ime.';
    } elseif (mb_strlen($ime) > 100) {
        $greske['ime'] = 'Ime može imati najviše 100 znakova.';
    }

    if ($email === '') {
        $greske['email'] = 'Upiši email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 190) {
        $greske['email'] = 'Email nije ispravan.';
    } elseif (korisnik_email_zauzet($email, $osim_id)) {
        $greske['email'] = 'Račun s tim emailom već postoji.';
    }

    return $greske;
}

function provjeri_novu_lozinku($lozinka, $lozinka_ponovo)
{
    $greske = [];

    if (mb_strlen($lozinka) < 8) {
        $greske['lozinka'] = 'Lozinka mora imati najmanje 8 znakova.';
    }

    if ($lozinka_ponovo !== $lozinka) {
        $greske['lozinka_ponovo'] = 'Lozinke se ne podudaraju.';
    }

    return $greske;
}

// Provjera podataka za registraciju
function provjeri_registraciju($ime, $email, $lozinka, $lozinka_ponovo)
{
    return provjeri_ime_i_email($ime, $email) + provjeri_novu_lozinku($lozinka, $lozinka_ponovo);
}

function stranica_registracija()
{
    $ime = '';
    $email = '';
    $greske = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ime = trim(post_tekst('ime'));
        $email = trim(post_tekst('email'));
        $lozinka = post_tekst('lozinka');
        $lozinka_ponovo = post_tekst('lozinka_ponovo');

        $greske = provjeri_registraciju($ime, $email, $lozinka, $lozinka_ponovo);

        if (count($greske) === 0) {
            korisnik_dodaj($ime, $email, $lozinka);
            postavi_poruku('uspjeh', 'Račun je napravljen. Sada se možeš prijaviti.');
            preusmjeri('index.php?stranica=prijava');
        }
    }

    prikazi('registracija', [
        'ime' => $ime,
        'email' => $email,
        'greske' => $greske,
    ]);
}

function stranica_prijava()
{
    // Već prijavljeni korisnik se redirecta na početnu stranicu
    if (trenutni_korisnik() !== null) {
        preusmjeri('index.php');
    }

    $email = '';
    $greska = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim(post_tekst('email'));
        $lozinka = post_tekst('lozinka');

        $korisnik = korisnik_po_emailu($email);

        if ($korisnik === null || !password_verify($lozinka, $korisnik['lozinka_hash'])) {
            $greska = 'Pogrešan email ili lozinka.';
        } elseif (!$korisnik['aktivan']) {
            $greska = 'Ovaj račun je deaktiviran.';
        } else {
            prijavi($korisnik['id']);
            postavi_poruku('uspjeh', 'Dobro došao, ' . $korisnik['ime'] . '!');
            preusmjeri('index.php');
        }
    }

    prikazi('prijava', [
        'email' => $email,
        'greska' => $greska,
    ]);
}

// Odjava je dopuštena samo POST zahtjevom
function stranica_odjava()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        odjavi();
        postavi_poruku('uspjeh', 'Odjavljen si.');
    }

    preusmjeri('index.php');
}

// Profil prijavljenog korisnika. Pregled i promjena imena i emaila
function stranica_profil()
{
    $korisnik = zahtijevaj_prijavu();

    $ime = $korisnik['ime'];
    $email = $korisnik['email'];
    $greske = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ime = trim(post_tekst('ime'));
        $email = trim(post_tekst('email'));

        $greske = provjeri_ime_i_email($ime, $email, $korisnik['id']);

        if (count($greske) === 0) {
            korisnik_uredi_profil($korisnik['id'], $ime, $email);
            postavi_poruku('uspjeh', 'Profil je spremljen.');
            preusmjeri('index.php?stranica=profil');
        }
    }

    prikazi('profil', [
        'korisnik' => $korisnik,
        'ime' => $ime,
        'email' => $email,
        'greske' => $greske,
    ]);
}

// Promjena lozinke prijavljenog korisnika
function stranica_lozinka()
{
    $korisnik = zahtijevaj_prijavu();
    $greske = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $trenutna = post_tekst('trenutna');
        $lozinka = post_tekst('lozinka');
        $lozinka_ponovo = post_tekst('lozinka_ponovo');

        if (!password_verify($trenutna, korisnik_lozinka_hash($korisnik['id']))) {
            $greske['trenutna'] = 'Trenutna lozinka nije točna.';
        }

        $greske += provjeri_novu_lozinku($lozinka, $lozinka_ponovo);

        if (count($greske) === 0) {
            korisnik_promijeni_lozinku($korisnik['id'], $lozinka);
            session_regenerate_id(true);
            postavi_poruku('uspjeh', 'Lozinka je promijenjena.');
            preusmjeri('index.php?stranica=profil');
        }
    }

    prikazi('lozinka', [
        'greske' => $greske,
    ]);
}
