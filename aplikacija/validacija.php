<?php
// Provjera podataka prema JSON Schemi (biblioteka opis/json-schema).
// Sheme su u aplikacija/api/sheme/, a $id svake sheme počinje s ADRESA_SHEMA.

const ADRESA_SHEMA = 'https://mjestopis.test/api/sheme/';
const MAPA_SHEMA = __DIR__ . '/api/sheme';

// Provjerava podatke (rezultat json_decode() bez pretvaranja u polje) prema shemi, npr. 'vijest-nova'.
// Vraća polje grešaka po nazivu polja (prazno ako su podaci ispravni). Greške cijelog tijela su pod ključem 'tijelo'.
function provjeri_shemu($podaci, $ime_sheme)
{
    static $validator = null;

    if ($validator === null) {
        // Prijavljuju se sve greške, a ne samo prva
        $validator = new Opis\JsonSchema\Validator(null, 50, false);
        $validator->resolver()->registerPrefix(ADRESA_SHEMA, MAPA_SHEMA);
    }

    $rezultat = $validator->validate($podaci, ADRESA_SHEMA . $ime_sheme . '.json');

    $greske = [];
    if (!$rezultat->isValid()) {
        greske_sheme($rezultat->error(), $greske);
    }

    return $greske;
}

// Prolazi kroz stablo grešaka biblioteke i za svako polje zapisuje prvu poruku na hrvatskom
function greske_sheme($greska, &$greske)
{
    if (count($greska->subErrors()) > 0) {
        foreach ($greska->subErrors() as $podgreska) {
            greske_sheme($podgreska, $greske);
        }
        return;
    }

    $putanja = $greska->data()->fullPath();
    $polje = count($putanja) > 0 ? implode('.', $putanja) : 'tijelo';
    $argumenti = $greska->args();

    // Obavezna i nedopuštena polja prijavljuju se na samom polju, a ne na cijelom tijelu
    if ($greska->keyword() === 'required') {
        foreach ($argumenti['missing'] as $nedostaje) {
            $greske[$nedostaje] ??= 'Obavezno polje.';
        }
        return;
    }
    if ($greska->keyword() === 'additionalProperties') {
        foreach ($argumenti['properties'] as $visak) {
            $greske[$visak] ??= 'Polje nije dopušteno.';
        }
        return;
    }

    // Kod enum biblioteka ne šalje popis dopuštenih vrijednosti, pa se čita iz sheme
    if ($greska->keyword() === 'enum') {
        $argumenti['enum'] = $greska->schema()->info()->data()->enum ?? [];
    }

    $greske[$polje] ??= poruka_sheme($greska->keyword(), $argumenti);
}

// Poruka na hrvatskom za ključnu riječ sheme koja nije zadovoljena
function poruka_sheme($kljucna_rijec, $argumenti)
{
    $tipovi = ['string' => 'tekst', 'integer' => 'cijeli broj', 'number' => 'broj', 'boolean' => 'true ili false',
               'null' => 'null', 'object' => 'objekt', 'array' => 'polje'];

    switch ($kljucna_rijec) {
        case 'type':
            $ocekivano = array_map(fn ($tip) => $tipovi[$tip] ?? $tip, (array) $argumenti['expected']);
            return 'Mora biti ' . implode(' ili ', $ocekivano) . '.';
        case 'minLength':
            return $argumenti['min'] === 1 ? 'Ne smije biti prazno.' : 'Najmanje ' . $argumenti['min'] . ' znakova.';
        case 'maxLength':
            return 'Najviše ' . $argumenti['max'] . ' znakova.';
        case 'pattern':
            return 'Ne smije biti prazno.';
        case 'minimum':
            return 'Najmanje ' . $argumenti['min'] . '.';
        case 'enum':
            return 'Dopuštene vrijednosti: ' . implode(', ', $argumenti['enum']) . '.';
        case 'format':
            return 'Mora biti datum i vrijeme, npr. 2026-12-01T09:00:00+01:00.';
        case 'minProperties':
            return 'Pošalji barem jedno polje.';
        default:
            return 'Neispravna vrijednost.';
    }
}
