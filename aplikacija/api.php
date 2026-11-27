<?php
// Zajedničke funkcije vlastitog REST API-ja (javno/api.php).
// Svaki odgovor je JSON s istom strukturom:
// uspjeh {"podaci": …}, greška {"greska": {"status": …, "poruka": "…", "polja": {…}}}.

// Ispisuje JSON i završava zahtjev
function api_ispisi($tijelo, $status)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($tijelo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

// Uspješan odgovor. $dodatno su ostali ključevi uz "podaci" (npr. "stranicenje").
function api_odgovor($podaci, $status = 200, $dodatno = [])
{
    api_ispisi(['podaci' => $podaci] + $dodatno, $status);
}

// Odgovor 204 nema tijelo, pa ni Content-Type
function api_bez_sadrzaja()
{
    http_response_code(204);
    exit;
}

// Greška. $polja su poruke po poljima kod neispravnog unosa.
function api_greska($status, $poruka, $polja = [])
{
    $greska = ['status' => $status, 'poruka' => $poruka];
    if (count($polja) > 0) {
        $greska['polja'] = $polja;
    }

    api_ispisi(['greska' => $greska], $status);
}

// Neuhvaćena greška: detalji idu u log, a klijent dobiva samo 500
function obradi_gresku_api($greska)
{
    error_log($greska->getMessage() . ' u ' . $greska->getFile() . ':' . $greska->getLine());

    if (headers_sent()) {
        return;
    }

    try {
        api_greska(500, 'Došlo je do greške na poslužitelju.');
    } catch (Throwable $druga) {
        exit;
    }
}

// Tijelo zahtjeva (POST, PATCH) kao polje. Tijelo mora biti JSON objekt
// poslan sa zaglavljem Content-Type: application/json, inače odgovor je 415 ili 400.
function api_tijelo()
{
    $vrsta = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
    if ($vrsta !== 'application/json') {
        api_greska(415, 'Tijelo zahtjeva mora biti JSON (Content-Type: application/json).');
    }

    $json = file_get_contents('php://input');
    $podaci = json_decode($json);
    if (json_last_error() !== JSON_ERROR_NONE) {
        api_greska(400, 'Tijelo zahtjeva nije ispravan JSON.');
    }
    if (!is_object($podaci)) {
        api_greska(400, 'Tijelo zahtjeva mora biti JSON objekt.');
    }

    return json_decode($json, true);
}

// Token iz zaglavlja Authorization: Bearer <token> ili null
function api_bearer_token()
{
    $zaglavlje = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if ($zaglavlje === '' && function_exists('getallheaders')) {
        $zaglavlje = array_change_key_case(getallheaders())['authorization'] ?? '';
    }

    if (!preg_match('/^Bearer\s+([0-9a-f]{64})$/i', trim($zaglavlje), $dijelovi)) {
        return null;
    }

    return strtolower($dijelovi[1]);
}

// Prijavljeni korisnik API-ja (kao trenutni_korisnik() na stranici) ili null.
// Korisnik se čita iz baze na svakom zahtjevu, pa deaktivacija odmah vrijedi.
// Vraća i 'token_id' da se token može obrisati kod odjave.
function api_korisnik()
{
    static $korisnik = false;

    if ($korisnik === false) {
        $korisnik = null;
        $token = api_bearer_token();
        $zapis = $token === null ? null : token_vazeci($token);

        if ($zapis !== null) {
            $korisnik = korisnik_po_id($zapis['korisnik_id']);
            if ($korisnik === null || !$korisnik['aktivan']) {
                $korisnik = null;
            } else {
                $korisnik['token_id'] = $zapis['id'];
            }
        }
    }

    return $korisnik;
}

// Bez valjanog tokena odgovor je 401
function api_zahtijevaj_prijavu()
{
    $korisnik = api_korisnik();

    if ($korisnik === null) {
        header('WWW-Authenticate: Bearer');
        api_greska(401, 'Potrebna je prijava. Pošalji valjani token u zaglavlju Authorization: Bearer <token>.');
    }

    return $korisnik;
}

// Prijavljeni korisnik bez potrebne uloge dobiva 403
function api_zahtijevaj_ulogu($uloge)
{
    $korisnik = api_zahtijevaj_prijavu();

    if (!in_array($korisnik['uloga'], $uloge, true)) {
        api_greska(403, 'Nemaš ovlasti za ovu radnju.');
    }

    return $korisnik;
}

// Pozitivan cijeli broj iz adrese (?kljuc=5). Ako ga nema, vraća $zadano.
// Neispravna vrijednost ili broj izvan granica dodaje poruku u $greske.
function api_get_broj($kljuc, &$greske, $zadano = 0, $najvise = null)
{
    if (!isset($_GET[$kljuc])) {
        return $zadano;
    }

    $vrijednost = $_GET[$kljuc];
    if (!is_string($vrijednost) || !ctype_digit($vrijednost) || (int) $vrijednost < 1) {
        $greske[$kljuc] = 'Mora biti pozitivan cijeli broj.';
        return $zadano;
    }

    if ($najvise !== null && (int) $vrijednost > $najvise) {
        $greske[$kljuc] = 'Najviše ' . $najvise . '.';
        return $zadano;
    }

    return (int) $vrijednost;
}

// Tekst iz adrese, bez razmaka na početku i kraju. Predug tekst dodaje poruku u $greske.
function api_get_tekst($kljuc, &$greske, $najvise = 100)
{
    $vrijednost = $_GET[$kljuc] ?? '';

    if (!is_string($vrijednost)) {
        $greske[$kljuc] = 'Mora biti tekst.';
        return '';
    }

    $vrijednost = trim($vrijednost);
    if (mb_strlen($vrijednost) > $najvise) {
        $greske[$kljuc] = 'Najviše ' . $najvise . ' znakova.';
        return '';
    }

    return $vrijednost;
}

// Straničenje popisa: ?stranica=2&po_stranici=10.
// Vraća [stranica, po_stranici]. Stranica iza zadnje daje prazan popis.
function api_stranicenje(&$greske, $zadano = 10, $najvise = 50)
{
    return [
        api_get_broj('stranica', $greske, 1),
        api_get_broj('po_stranici', $greske, $zadano, $najvise),
    ];
}

// Podaci o straničenju za odgovor
function api_podaci_stranicenja($stranica, $po_stranici, $ukupno)
{
    return [
        'stranica' => $stranica,
        'po_stranici' => $po_stranici,
        'ukupno' => $ukupno,
        'broj_stranica' => max(1, (int) ceil($ukupno / $po_stranici)),
    ];
}

// Apsolutna adresa unutar stranice (npr. za slike), jer relativna adresa
// u JSON-u ne bi vrijedila u odnosu na api.php/v1/…
function api_adresa($putanja)
{
    if (defined('HTTPS_ADRESA') && HTTPS_ADRESA !== '') {
        return rtrim(HTTPS_ADRESA, '/') . '/' . $putanja;
    }

    $protokol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $mapa = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');

    return $protokol . '://' . $_SERVER['HTTP_HOST'] . $mapa . '/' . $putanja;
}

// Datum iz baze u obliku ISO 8601 (npr. 2026-11-20T10:00:00+01:00)
function api_datum($vrijednost)
{
    return $vrijednost === null ? null : date(DATE_ATOM, strtotime($vrijednost));
}

// Rastavlja putanju iza api.php (npr. /v1/vijesti/5) na verziju, resurs i id.
// Vraća null ako putanja nije tog oblika.
function api_putanja()
{
    $dijelovi = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));

    if (count($dijelovi) < 2 || count($dijelovi) > 3) {
        return null;
    }

    $id = null;
    if (count($dijelovi) === 3) {
        // id je samo pozitivan cijeli broj
        if (!ctype_digit($dijelovi[2]) || (int) $dijelovi[2] < 1) {
            return null;
        }
        $id = (int) $dijelovi[2];
    }

    return ['verzija' => $dijelovi[0], 'resurs' => $dijelovi[1], 'id' => $id];
}

// Poziva funkciju za resurs i metodu.
// $rute: 'resurs' (popis) ili 'resurs/id' (jedan zapis) => ['METODA' => 'ime_funkcije'].
function api_usmjeri($rute)
{
    $putanja = api_putanja();

    if ($putanja === null || $putanja['verzija'] !== 'v1') {
        api_greska(404, 'Tražena adresa ne postoji.');
    }

    $kljuc = $putanja['resurs'] . ($putanja['id'] === null ? '' : '/id');
    if (!isset($rute[$kljuc])) {
        api_greska(404, 'Tražena adresa ne postoji.');
    }

    $metoda = $_SERVER['REQUEST_METHOD'];
    if (!isset($rute[$kljuc][$metoda])) {
        header('Allow: ' . implode(', ', array_keys($rute[$kljuc])));
        api_greska(405, 'Metoda ' . $metoda . ' nije dopuštena na ovoj adresi.');
    }

    $rute[$kljuc][$metoda]($putanja['id']);
}
