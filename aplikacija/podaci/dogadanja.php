<?php
// Događanja i vrste događanja

function vrste_dogadanja_sve()
{
    return db_svi_retci('SELECT id, naziv FROM vrste_dogadanja ORDER BY naziv');
}

// Javno se vide objavljena događanja koja još nisu završila (današnja se vide cijeli dan).
// $filtar: zupanija, grad_opcina, vrsta (id-ovi, 0 = bez filtra), od i do (datumi 'Y-m-d', do može biti null).
// Događanje je u razdoblju ako se s njim preklapa: završava od datuma od, a počinje najkasnije na datum do.
// Vraća WHERE uvjet i parametre, zajedničke za popis i za brojanje.
function dogadanja_javna_uvjeti($filtar)
{
    $uvjeti = 'd.objavljeno = 1 AND COALESCE(d.zavrsetak, d.pocetak) >= ?';
    $parametri = [$filtar['od']];

    if ($filtar['do'] !== null) {
        $uvjeti .= ' AND DATE(d.pocetak) <= ?';
        $parametri[] = $filtar['do'];
    }

    if (($filtar['grad_opcina'] ?? 0) > 0) {
        $uvjeti .= ' AND d.grad_opcina_id = ?';
        $parametri[] = (int) $filtar['grad_opcina'];
    } elseif (($filtar['zupanija'] ?? 0) > 0) {
        $uvjeti .= ' AND g.zupanija_id = ?';
        $parametri[] = (int) $filtar['zupanija'];
    }

    if (($filtar['vrsta'] ?? 0) > 0) {
        $uvjeti .= ' AND d.vrsta_id = ?';
        $parametri[] = (int) $filtar['vrsta'];
    }

    return [$uvjeti, $parametri];
}

// Broj javnih događanja za filtar (za straničenje)
function dogadanja_javna_broj($filtar)
{
    [$uvjeti, $parametri] = dogadanja_javna_uvjeti($filtar);

    $redak = db_jedan_redak(
        "SELECT COUNT(*) AS broj
         FROM dogadanja d
         JOIN gradovi_opcine g ON g.id = d.grad_opcina_id
         WHERE $uvjeti",
        $parametri
    );

    return (int) $redak['broj'];
}

// Jedna stranica javnih događanja za filtar, po danu pa po vremenu početka.
// dan je dan pod kojim se događanje prikazuje: dan početka, a za događanje koje je već počelo prvi dan razdoblja (od).
function dogadanja_javna($filtar, $koliko, $preskoci)
{
    [$uvjeti, $parametri] = dogadanja_javna_uvjeti($filtar);
    array_unshift($parametri, $filtar['od']);
    $parametri[] = (int) $koliko;
    $parametri[] = (int) $preskoci;

    return db_svi_retci(
        "SELECT d.id, d.naziv, d.pocetak, d.zavrsetak, d.cijena, d.adresa, d.na_otvorenom,
                GREATEST(DATE(d.pocetak), ?) AS dan,
                v.naziv AS vrsta, g.naziv AS grad_opcina, l.naziv AS lokacija
         FROM dogadanja d
         JOIN vrste_dogadanja v ON v.id = d.vrsta_id
         JOIN gradovi_opcine g ON g.id = d.grad_opcina_id
         LEFT JOIN lokacije l ON l.id = d.lokacija_id
         WHERE $uvjeti
         ORDER BY dan, TIME(d.pocetak), d.naziv, d.id
         LIMIT ? OFFSET ?",
        $parametri
    );
}

// Jedno javno (objavljeno) događanje s vrstom, područjem i lokacijom. Prošla događanja se također vraćaju.
// lokacija_objavljena govori smije li se na lokaciju postaviti poveznica. Skriveno ili nepostojeće vraća null.
function dogadanje_javno_po_id($id)
{
    return db_jedan_redak(
        'SELECT d.id, d.naziv, d.opis, d.adresa, d.pocetak, d.zavrsetak, d.cijena, d.na_otvorenom, d.slika_id, d.lokacija_id,
                v.naziv AS vrsta, g.naziv AS grad_opcina, z.naziv AS zupanija,
                l.naziv AS lokacija, l.objavljeno AS lokacija_objavljena
         FROM dogadanja d
         JOIN vrste_dogadanja v ON v.id = d.vrsta_id
         JOIN gradovi_opcine g ON g.id = d.grad_opcina_id
         JOIN zupanije z ON z.id = g.zupanija_id
         LEFT JOIN lokacije l ON l.id = d.lokacija_id
         WHERE d.id = ? AND d.objavljeno = 1',
        [(int) $id]
    );
}

