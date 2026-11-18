<?php
// Zaštita obrazaca od CSRF napada.
// Svaka sesija dobiva tajni token. Naši obrasci ga šalju u skrivenom polju, a obrazac
// s tuđe stranice ga ne može znati, pa poslužitelj takav zahtjev odbija.

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Skriveno polje koje se stavlja u svaki obrazac koji šalje POST.
function csrf_polje()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// Vraća true ako je poslani token jednak tokenu iz sesije.
function csrf_ispravan()
{
    $poslani = post_tekst('csrf_token');

    return $poslani !== '' && hash_equals(csrf_token(), $poslani);
}

// Čišćenje HTML-a iz editora (Quill) prije spremanja u bazu, a kao zaštita od XSS napada.
// Symfony HtmlSanitizer ostavlja samo oznake i atribute s popisa dopuštenih, a briše sve ostalo.
function ocisti_html($html)
{
    static $sanitizer = null;

    if ($sanitizer === null) {
        $postavke = (new Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig())
            ->allowElement('p')
            ->allowElement('br')
            ->allowElement('h2')
            ->allowElement('h3')
            ->allowElement('strong')
            ->allowElement('em')
            ->allowElement('a', ['href'])
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('blockquote')
            ->allowLinkSchemes(['http', 'https', 'mailto'])
            // Bez ograničenja duljine
            ->withMaxInputLength(-1);

        $sanitizer = new Symfony\Component\HtmlSanitizer\HtmlSanitizer($postavke);
    }

    return trim($sanitizer->sanitize($html));
}

// Prijenos slika
// Slike se spremaju u mapu prijenosi
const MAPA_PRIJENOSA = __DIR__ . '/../prijenosi';

// Dopuštene vrste slika i nastavak datoteke za svaku
const VRSTE_SLIKA = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

// Provjerava poslanu sliku, smanjuje je i sprema ispočetka pod nasumičnim nazivom.
// Vraća [podaci o slici, null] ili [null, poruka greške za korisnika].
function prijenos_slike($datoteka)
{
    if (($datoteka['error'] ?? null) !== UPLOAD_ERR_OK || !is_uploaded_file($datoteka['tmp_name'])) {
        return [null, 'Slika nije prenesena. Pokušaj ponovno.'];
    }

    if ($datoteka['size'] > 5 * 1024 * 1024) {
        return [null, 'Slika može imati najviše 5 MB.'];
    }

    // Vrsta se čita iz sadržaja datoteke, a ne iz naziva
    $vrsta = mime_content_type($datoteka['tmp_name']);

    if (!isset(VRSTE_SLIKA[$vrsta])) {
        return [null, 'Dopuštene su samo slike JPEG, PNG i WebP.'];
    }

    // Slika se učitava i sprema ispočetka, pa nestaje sve što nije slika 
    $slika = @imagecreatefromstring(file_get_contents($datoteka['tmp_name']));

    if ($slika === false) {
        return [null, 'Datoteka nije ispravna slika.'];
    }

    // Slike šire od 1600px se smanjuju
    if (imagesx($slika) > 1600) {
        $slika = imagescale($slika, 1600);
    }

    // Čuva prozirnost PNG i WebP slika
    imagesavealpha($slika, true);

    if (!is_dir(MAPA_PRIJENOSA)) {
        mkdir(MAPA_PRIJENOSA);
    }

    $naziv = bin2hex(random_bytes(16)) . '.' . VRSTE_SLIKA[$vrsta];
    $putanja = MAPA_PRIJENOSA . '/' . $naziv;

    match ($vrsta) {
        'image/jpeg' => imagejpeg($slika, $putanja, 85),
        'image/png' => imagepng($slika, $putanja),
        'image/webp' => imagewebp($slika, $putanja, 85),
    };

    return [[
        'datoteka' => $naziv,
        'izvorni_naziv' => mb_substr($datoteka['name'], 0, 255),
        'vrsta' => $vrsta,
        'velicina' => filesize($putanja),
        'sirina' => imagesx($slika),
        'visina' => imagesy($slika),
    ], null];
}

// Briše datoteku slike iz mape prijenosi/ (npr. kad se slika zamijeni)
function obrisi_datoteku_slike($datoteka)
{
    $putanja = MAPA_PRIJENOSA . '/' . basename($datoteka);

    if (is_file($putanja)) {
        unlink($putanja);
    }
}
