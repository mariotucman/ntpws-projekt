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
