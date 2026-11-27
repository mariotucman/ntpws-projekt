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

// Odjava: briše podatke sesije, uništava sesiju na poslužitelju i briše kolačić u pregledniku.
// Zatim se pokreće nova prazna sesija s novim ID-om, samo za poruku „Odjavljen si.”.
function odjavi()
{
    $_SESSION = [];

    $kolacic = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $kolacic['path'],
        'domain' => $kolacic['domain'],
        'secure' => $kolacic['secure'],
        'httponly' => $kolacic['httponly'],
        'samesite' => $kolacic['samesite'],
    ]);

    session_destroy();

    session_id(session_create_id());
    session_start();
}

// Korisnik se na svakom zahtjevu dohvaća iz baze.
// Ako je račun u međuvremenu deaktiviran ili obrisan, korisnika se odjavljuje.
function trenutni_korisnik()
{
    static $korisnik = false;

    if ($korisnik === false) {
        $korisnik = null;
        if (isset($_SESSION['korisnik_id'])) {
            $korisnik = korisnik_po_id($_SESSION['korisnik_id']);

            if ($korisnik === null || !$korisnik['aktivan']) {
                $korisnik = null;
                odjavi();
                postavi_poruku('greska', 'Tvoj račun je deaktiviran pa si odjavljen.');
            }
        }
    }

    return $korisnik;
}

// Neprijavljenog posjetitelja se šalje na prijavu
function zahtijevaj_prijavu()
{
    if (trenutni_korisnik() === null) {
        postavi_poruku('greska', 'Za ovu stranicu se prvo prijavi.');
        preusmjeri('index.php?stranica=prijava');
    }

    return trenutni_korisnik();
}

// Vraća true ako je korisnik prijavljen i ima jednu od navedenih uloga
function ima_ulogu($uloge)
{
    $korisnik = trenutni_korisnik();

    return $korisnik !== null && in_array($korisnik['uloga'], $uloge, true);
}

// Neprijavljenog posjetitelja šalje na prijavu, a prijavljenom bez potrebne uloge prikazuje stranicu 403
function zahtijevaj_ulogu($uloge)
{
    $korisnik = zahtijevaj_prijavu();

    if (!ima_ulogu($uloge)) {
        stranica_zabranjeno('Nemaš ovlasti za ovu stranicu.');
        exit;
    }

    return $korisnik;
}

// Administrator smije uređivati sve vijesti, a urednik samo one kojima je autor
function smije_urediti_vijest($vijest)
{
    $korisnik = trenutni_korisnik();

    if ($korisnik === null) {
        return false;
    }

    return $korisnik['uloga'] === 'administrator'
        || ($korisnik['uloga'] === 'urednik' && (int) $vijest['autor_id'] === (int) $korisnik['id']);
}
