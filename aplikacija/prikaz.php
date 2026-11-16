<?php

function prikazi($predlozak, $podaci = [])
{
    extract($podaci);

    ob_start();
    require __DIR__ . '/../predlosci/' . $predlozak . '.php';
    $sadrzaj = ob_get_clean();

    require __DIR__ . '/../predlosci/izgled.php';
}

// Zaštita teksta za ispis u HTML-u
function e($tekst)
{
    return htmlspecialchars((string) $tekst, ENT_QUOTES, 'UTF-8');
}

// Šalje preglednik na drugu adresu i prekida izvođenje.
function preusmjeri($adresa)
{
    header('Location: ' . $adresa);
    exit;
}

// Vraća tekst poslan obrascem. Ako polje ne postoji ili nije tekst, vraća prazan tekst.
function post_tekst($kljuc)
{
    $vrijednost = $_POST[$kljuc] ?? '';

    return is_string($vrijednost) ? $vrijednost : '';
}

// Vraća pozitivan cijeli broj iz adrese. Ako ga nema ili nije ispravan, vraća 0.
function get_broj($kljuc)
{
    $vrijednost = $_GET[$kljuc] ?? '';

    return is_string($vrijednost) && ctype_digit($vrijednost) ? (int) $vrijednost : 0;
}

// Vraća tekst iz adrese, bez razmaka na početku i kraju i najviše $najvise znakova.
function get_tekst($kljuc, $najvise = 100)
{
    $vrijednost = $_GET[$kljuc] ?? '';

    return is_string($vrijednost) ? mb_substr(trim($vrijednost), 0, $najvise) : '';
}

// Sprema poruku koja će se prikazati na sljedećoj stranici (nakon preusmjeravanja).
// Vrsta je 'uspjeh' ili 'greska'.
function postavi_poruku($vrsta, $tekst)
{
    $_SESSION['poruka'] = ['vrsta' => $vrsta, 'tekst' => $tekst];
}

// Vraća spremljenu poruku i briše je, da se prikaže samo jednom.
function uzmi_poruku()
{
    $poruka = $_SESSION['poruka'] ?? null;
    unset($_SESSION['poruka']);

    return $poruka;
}

// Prvo slovo imena, npr. "Ivan Horvat" -> "I".
function inicijali($ime)
{
    return mb_strtoupper(mb_substr(trim($ime), 0, 1));
}
