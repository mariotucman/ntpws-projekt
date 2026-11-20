<?php

function vrste_lokacija_sve()
{
    return db_svi_retci('SELECT id, naziv FROM vrste_lokacija ORDER BY naziv');
}

// Javno se vide samo objavljene lokacije
// $filtar: zupanija, grad_opcina, vrsta (id-ovi, 0 = bez filtra) i trazi (tekst u nazivu ili opisu)
// Vraća WHERE uvjet i parametre, zajedničke za popis i za brojanje lokacija.
function lokacije_javne_uvjeti($filtar)
{
    $uvjeti = 'l.objavljeno = 1';
    $parametri = [];

    if (($filtar['grad_opcina'] ?? 0) > 0) {
        $uvjeti .= ' AND l.grad_opcina_id = ?';
        $parametri[] = (int) $filtar['grad_opcina'];
    } elseif (($filtar['zupanija'] ?? 0) > 0) {
        $uvjeti .= ' AND g.zupanija_id = ?';
        $parametri[] = (int) $filtar['zupanija'];
    }

    if (($filtar['vrsta'] ?? 0) > 0) {
        $uvjeti .= ' AND l.vrsta_id = ?';
        $parametri[] = (int) $filtar['vrsta'];
    }

    if (($filtar['trazi'] ?? '') !== '') {
        $uzorak = uzorak_pretrage($filtar['trazi']);
        $uvjeti .= " AND (l.naziv LIKE ? ESCAPE '|' OR l.opis LIKE ? ESCAPE '|')";
        $parametri[] = $uzorak;
        $parametri[] = $uzorak;
    }

    return [$uvjeti, $parametri];
}

// Broj javnih lokacija za filtar (za straničenje)
function lokacije_javne_broj($filtar = [])
{
    [$uvjeti, $parametri] = lokacije_javne_uvjeti($filtar);

    $redak = db_jedan_redak(
        "SELECT COUNT(*) AS broj
         FROM lokacije l
         JOIN gradovi_opcine g ON g.id = l.grad_opcina_id
         WHERE $uvjeti",
        $parametri
    );

    return (int) $redak['broj'];
}

// Jedna stranica javnih lokacija za filtar, poredanih po nazivu
function lokacije_javne($filtar = [], $koliko = 10, $preskoci = 0)
{
    [$uvjeti, $parametri] = lokacije_javne_uvjeti($filtar);
    $parametri[] = (int) $koliko;
    $parametri[] = (int) $preskoci;

    return db_svi_retci(
        "SELECT l.id, l.naziv, l.opis, v.naziv AS vrsta, g.naziv AS grad_opcina, z.naziv AS zupanija
         FROM lokacije l
         JOIN vrste_lokacija v ON v.id = l.vrsta_id
         JOIN gradovi_opcine g ON g.id = l.grad_opcina_id
         JOIN zupanije z ON z.id = g.zupanija_id
         WHERE $uvjeti
         ORDER BY l.naziv, l.id
         LIMIT ? OFFSET ?",
        $parametri
    );
}

// Jedna javna lokacija s vrstom i područjem. Skrivena ili nepostojeća vraća null.
function lokacija_javna_po_id($id)
{
    return db_jedan_redak(
        'SELECT l.id, l.naziv, l.opis, l.adresa, l.lat, l.lng, l.radno_vrijeme,
                v.naziv AS vrsta, g.naziv AS grad_opcina, z.naziv AS zupanija
         FROM lokacije l
         JOIN vrste_lokacija v ON v.id = l.vrsta_id
         JOIN gradovi_opcine g ON g.id = l.grad_opcina_id
         JOIN zupanije z ON z.id = g.zupanija_id
         WHERE l.id = ? AND l.objavljeno = 1',
        [(int) $id]
    );
}

// Radno vrijeme iz JSON-a za prikaz: ['dani' => [[dan, tekst, danas], …], 'napomena' => tekst ili null]
// Lokacija bez radnog vremena (NULL ili neispravan JSON) vraća null, što znači da je uvijek otvorena.
// Dan koji nema dva vremena smatra se zatvorenim.
function radno_vrijeme_za_prikaz($json)
{
    $podaci = $json !== null ? json_decode($json, true) : null;

    if (!is_array($podaci)) {
        return null;
    }

    $nazivi = ['pon' => 'Ponedjeljak', 'uto' => 'Utorak', 'sri' => 'Srijeda', 'cet' => 'Četvrtak',
        'pet' => 'Petak', 'sub' => 'Subota', 'ned' => 'Nedjelja'];
    $danas = array_keys($nazivi)[(int) date('N') - 1];
    $dani = [];

    foreach ($nazivi as $kljuc => $naziv) {
        $vrijeme = $podaci[$kljuc] ?? null;
        $tekst = is_array($vrijeme) && count($vrijeme) === 2 ? $vrijeme[0] . ' – ' . $vrijeme[1] : 'Zatvoreno';
        $dani[] = [$naziv, $tekst, $kljuc === $danas];
    }

    $napomena = $podaci['napomena'] ?? null;

    return ['dani' => $dani, 'napomena' => is_string($napomena) && $napomena !== '' ? $napomena : null];
}
