<?php
// Prijava korisnika i sesija

// Kolačić sesije nije dostupan JavaScriptu (httponly) i ne šalje se s tuđih stranica (samesite).
session_name('mjestopis');
session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => !empty($_SERVER['HTTPS']),
]);
session_start();

// Nakon uspješne prijave dobiva se novi ID sesije
function prijavi($korisnik_id)
{
    session_regenerate_id(true);
    $_SESSION['korisnik_id'] = $korisnik_id;
}

// Briše sve podatke sesije i daje novi ID
function odjavi()
{
    $_SESSION = [];
    session_regenerate_id(true);
}

function trenutni_korisnik()
{
    static $korisnik = false;

    if ($korisnik === false) {
        $korisnik = null;
        if (isset($_SESSION['korisnik_id'])) {
            $korisnik = korisnik_po_id($_SESSION['korisnik_id']);
        }
    }

    return $korisnik;
}
