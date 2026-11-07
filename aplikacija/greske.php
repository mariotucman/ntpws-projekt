<?php
// Korisnik vidi samo običnu poruku, a svi tehnički detalji zapisuju se u zapisi/greske.log

$mapa_zapisa = __DIR__ . '/../zapisi';
if (!is_dir($mapa_zapisa)) {
    mkdir($mapa_zapisa);
}

// Errori se zapisuju u log file
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', $mapa_zapisa . '/greske.log');

// Funkcija koja se poziva kad se dogodi greška
set_exception_handler('obradi_gresku');

function obradi_gresku($greska)
{
    error_log($greska->getMessage() . ' u ' . $greska->getFile() . ':' . $greska->getLine());

    if (!headers_sent()) {
        http_response_code(500);
    }

    echo '<!doctype html><html lang="hr"><head><meta charset="utf-8"><title>Greška | ERROR</title></head>';
    echo '<body><h1>Došlo je do greške</h1>';
    echo '<p><a href="index.php">Povratak na početnu stranicu</a></p></body></html>';
}
