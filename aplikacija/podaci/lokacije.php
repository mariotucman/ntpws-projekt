<?php

// Dani u tjednu: ključ u JSON-u radnog vremena => naziv za prikaz
const DANI_U_TJEDNU = [
    'pon' => 'Ponedjeljak',
    'uto' => 'Utorak',
    'sri' => 'Srijeda',
    'cet' => 'Četvrtak',
    'pet' => 'Petak',
    'sub' => 'Subota',
    'ned' => 'Nedjelja',
];

function vrste_lokacija_sve()
{
    return db_svi_retci('SELECT id, naziv FROM vrste_lokacija ORDER BY naziv');
}

// Vrste s brojem lokacija svake vrste (za administraciju)
function vrste_lokacija_s_brojem_lokacija()
{
    return db_svi_retci(
        'SELECT v.id, v.naziv, COUNT(l.id) AS broj_lokacija
         FROM vrste_lokacija v
         LEFT JOIN lokacije l ON l.vrsta_id = v.id
         GROUP BY v.id, v.naziv
         ORDER BY v.naziv'
    );
}

// Naziv je zauzet ako ga već ima druga vrsta
function vrsta_lokacije_naziv_zauzet($naziv, $osim_id = 0)
{
    $redak = db_jedan_redak('SELECT id FROM vrste_lokacija WHERE naziv = ? AND id <> ?', [$naziv, (int) $osim_id]);

    return $redak !== null;
}

function vrsta_lokacije_po_id($id)
{
    return db_jedan_redak('SELECT id, naziv FROM vrste_lokacija WHERE id = ?', [(int) $id]);
}

function vrsta_lokacije_dodaj($naziv)
{
    db_izvrsi('INSERT INTO vrste_lokacija (naziv) VALUES (?)', [$naziv]);
}

function vrsta_lokacije_uredi($id, $naziv)
{
    db_izvrsi('UPDATE vrste_lokacija SET naziv = ? WHERE id = ?', [$naziv, (int) $id]);
}

// Broj lokacija te vrste, uključujući skrivene
function vrsta_lokacije_broj_lokacija($id)
{
    $redak = db_jedan_redak('SELECT COUNT(*) AS broj FROM lokacije WHERE vrsta_id = ?', [(int) $id]);

    return (int) $redak['broj'];
}

// Briše vrstu. Ako je koristi neka lokacija, baza to odbija (RESTRICT) i funkcija vraća false.
function vrsta_lokacije_obrisi($id)
{
    try {
        db_izvrsi('DELETE FROM vrste_lokacija WHERE id = ?', [(int) $id]);
    } catch (mysqli_sql_exception $greska) {
        if ($greska->getCode() === 1451) {
            return false;
        }
        throw $greska;
    }

    return true;
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

    $danas = array_keys(DANI_U_TJEDNU)[(int) date('N') - 1];
    $dani = [];

    foreach (DANI_U_TJEDNU as $kljuc => $naziv) {
        $vrijeme = $podaci[$kljuc] ?? null;
        $tekst = is_array($vrijeme) && count($vrijeme) === 2 ? $vrijeme[0] . ' – ' . $vrijeme[1] : 'Zatvoreno';
        $dani[] = [$naziv, $tekst, $kljuc === $danas];
    }

    $napomena = $podaci['napomena'] ?? null;

    return ['dani' => $dani, 'napomena' => is_string($napomena) && $napomena !== '' ? $napomena : null];
}

// WHERE uvjet za administraciju: sve lokacije (i skrivene), uz pretragu po nazivu lokacije ili grada/općine
function lokacije_admin_uvjeti($trazi)
{
    if ($trazi === '') {
        return ['1 = 1', []];
    }

    $uzorak = uzorak_pretrage($trazi);

    return ["(l.naziv LIKE ? ESCAPE '|' OR g.naziv LIKE ? ESCAPE '|')", [$uzorak, $uzorak]];
}

// Broj lokacija za administraciju (za straničenje)
function lokacije_admin_broj($trazi)
{
    [$uvjeti, $parametri] = lokacije_admin_uvjeti($trazi);

    $redak = db_jedan_redak(
        "SELECT COUNT(*) AS broj
         FROM lokacije l
         JOIN gradovi_opcine g ON g.id = l.grad_opcina_id
         WHERE $uvjeti",
        $parametri
    );

    return (int) $redak['broj'];
}

// Jedna stranica lokacija za administraciju, zadnje izmijenjene prve
function lokacije_admin($trazi, $koliko, $preskoci)
{
    [$uvjeti, $parametri] = lokacije_admin_uvjeti($trazi);
    $parametri[] = (int) $koliko;
    $parametri[] = (int) $preskoci;

    return db_svi_retci(
        "SELECT l.id, l.naziv, l.objavljeno, l.istaknuto, l.azurirano,
                v.naziv AS vrsta, g.naziv AS grad_opcina, a.ime AS autor
         FROM lokacije l
         JOIN vrste_lokacija v ON v.id = l.vrsta_id
         JOIN gradovi_opcine g ON g.id = l.grad_opcina_id
         JOIN korisnici a ON a.id = l.autor_id
         WHERE $uvjeti
         ORDER BY l.azurirano DESC, l.id DESC
         LIMIT ? OFFSET ?",
        $parametri
    );
}

// Jedna lokacija bilo kojeg statusa, za uređivanje. Nepostojeća vraća null.
function lokacija_po_id($id)
{
    return db_jedan_redak(
        'SELECT l.id, l.vrsta_id, l.grad_opcina_id, g.zupanija_id, l.autor_id, l.naziv, l.opis, l.adresa, l.lat, l.lng,
                l.radno_vrijeme, l.objavljeno, l.istaknuto, l.kreirano, l.azurirano, a.ime AS autor
         FROM lokacije l
         JOIN gradovi_opcine g ON g.id = l.grad_opcina_id
         JOIN korisnici a ON a.id = l.autor_id
         WHERE l.id = ?',
        [(int) $id]
    );
}

// Naziv je zauzet ako u istom gradu/općini već postoji druga lokacija s tim nazivom
function lokacija_naziv_zauzet($naziv, $grad_opcina_id, $osim_id = 0)
{
    $redak = db_jedan_redak(
        'SELECT id FROM lokacije WHERE naziv = ? AND grad_opcina_id = ? AND id <> ?',
        [$naziv, (int) $grad_opcina_id, (int) $osim_id]
    );

    return $redak !== null;
}

function lokacija_dodaj($lokacija, $autor_id)
{
    db_izvrsi(
        'INSERT INTO lokacije (vrsta_id, grad_opcina_id, autor_id, naziv, opis, adresa, lat, lng, radno_vrijeme, objavljeno, istaknuto)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [$lokacija['vrsta_id'], $lokacija['grad_opcina_id'], (int) $autor_id, $lokacija['naziv'], $lokacija['opis'],
         $lokacija['adresa'], $lokacija['lat'], $lokacija['lng'], $lokacija['radno_vrijeme'], $lokacija['objavljeno'],
         $lokacija['istaknuto']]
    );
}

// Uređivanje ne mijenja autora
function lokacija_uredi($id, $lokacija)
{
    db_izvrsi(
        'UPDATE lokacije
         SET vrsta_id = ?, grad_opcina_id = ?, naziv = ?, opis = ?, adresa = ?, lat = ?, lng = ?, radno_vrijeme = ?,
             objavljeno = ?, istaknuto = ?
         WHERE id = ?',
        [$lokacija['vrsta_id'], $lokacija['grad_opcina_id'], $lokacija['naziv'], $lokacija['opis'], $lokacija['adresa'],
         $lokacija['lat'], $lokacija['lng'], $lokacija['radno_vrijeme'], $lokacija['objavljeno'], $lokacija['istaknuto'], (int) $id]
    );
}
