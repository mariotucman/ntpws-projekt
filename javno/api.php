<?php
// Ulaz vlastitog REST API-ja. Adrese su oblika api.php/v1/<resurs>[/<id>].

require __DIR__ . '/../aplikacija/greske.php';
require __DIR__ . '/../aplikacija/konfiguracija.php';
require __DIR__ . '/../aplikacija/baza.php';
require __DIR__ . '/../aplikacija/sigurnost.php';
require __DIR__ . '/../aplikacija/prikaz.php';
require __DIR__ . '/../aplikacija/api.php';
require __DIR__ . '/../aplikacija/podaci/korisnici.php';
require __DIR__ . '/../aplikacija/podaci/tokeni.php';
require __DIR__ . '/../aplikacija/podaci/kategorije.php';
require __DIR__ . '/../aplikacija/podaci/vijesti.php';
require __DIR__ . '/../aplikacija/api/tokeni.php';
require __DIR__ . '/../aplikacija/api/kategorije.php';
require __DIR__ . '/../aplikacija/api/vijesti.php';

// Greške API-ja vraćaju se kao JSON, a ne kao HTML stranica
set_exception_handler('obradi_gresku_api');

zahtijevaj_https();

// Popis dopuštenih adresa i metoda
$rute = [
    'tokeni' => ['POST' => 'api_token_prijava', 'DELETE' => 'api_token_odjava'],
    'kategorije' => ['GET' => 'api_kategorije_popis'],
    'vijesti' => ['GET' => 'api_vijesti_popis'],
    'vijesti/id' => ['GET' => 'api_vijest_detalj'],
];

api_usmjeri($rute);
