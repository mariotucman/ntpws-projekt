<?php

// Vraća tekst poslan obrascem. Ako polje ne postoji ili nije tekst, vraća prazan tekst.
function post_tekst($kljuc)
{
    $vrijednost = $_POST[$kljuc] ?? '';

    return is_string($vrijednost) ? $vrijednost : '';
}

// Provjerava podatke za registraciju.
function provjeri_registraciju($ime, $email, $lozinka, $lozinka_ponovo)
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
    } elseif (korisnik_email_zauzet($email)) {
        $greske['email'] = 'Račun s tim emailom već postoji.';
    }

    if (mb_strlen($lozinka) < 8) {
        $greske['lozinka'] = 'Lozinka mora imati najmanje 8 znakova.';
    }

    if ($lozinka_ponovo !== $lozinka) {
        $greske['lozinka_ponovo'] = 'Lozinke se ne podudaraju.';
    }

    return $greske;
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
            // Preusmjeravanje
            header('Location: index.php?stranica=registracija&uspjeh=1');
            exit;
        }
    }

    prikazi('registracija', [
        'ime' => $ime,
        'email' => $email,
        'greske' => $greske,
        'uspjeh' => isset($_GET['uspjeh']),
    ]);
}
