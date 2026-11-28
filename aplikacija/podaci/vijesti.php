<?php

// Uzorak za pretragu: traženi tekst može biti bilo gdje u stupcu.
// Znakovi % i _ traže se kao obični znakovi.
function uzorak_pretrage($tekst)
{
    return '%' . str_replace(['|', '%', '_'], ['||', '|%', '|_'], $tekst) . '%';
}

// Grad/općina i županija mogu biti NULL (vijest za cijelu Hrvatsku)
// Bez filtra se vide sve vijesti
// $filtar: zupanija, grad_opcina, kategorija (id-ovi, 0 = bez filtra) i trazi (tekst u naslovu ili sažetku)
// Vraća WHERE uvjet i parametre, zajedničke za popis i za brojanje vijesti.
function vijesti_javne_uvjeti($filtar)
{
    $uvjeti = "v.status = 'objavljeno' AND v.objavljeno_u <= NOW()";
    $parametri = [];

    if (($filtar['grad_opcina'] ?? 0) > 0) {
        $uvjeti .= ' AND v.grad_opcina_id = ?';
        $parametri[] = (int) $filtar['grad_opcina'];
    } elseif (($filtar['zupanija'] ?? 0) > 0) {
        $uvjeti .= ' AND g.zupanija_id = ?';
        $parametri[] = (int) $filtar['zupanija'];
    }

    if (($filtar['kategorija'] ?? 0) > 0) {
        $uvjeti .= ' AND v.kategorija_id = ?';
        $parametri[] = (int) $filtar['kategorija'];
    }

    if (($filtar['trazi'] ?? '') !== '') {
        $uzorak = uzorak_pretrage($filtar['trazi']);
        $uvjeti .= " AND (v.naslov LIKE ? ESCAPE '|' OR v.sazetak LIKE ? ESCAPE '|')";
        $parametri[] = $uzorak;
        $parametri[] = $uzorak;
    }

    return [$uvjeti, $parametri];
}

// Broj javnih vijesti za filtar (za straničenje)
function vijesti_javne_broj($filtar = [])
{
    [$uvjeti, $parametri] = vijesti_javne_uvjeti($filtar);

    $redak = db_jedan_redak(
        "SELECT COUNT(*) AS broj
         FROM vijesti v
         LEFT JOIN gradovi_opcine g ON g.id = v.grad_opcina_id
         WHERE $uvjeti",
        $parametri
    );

    return (int) $redak['broj'];
}

// Jedna stranica javnih vijesti za filtar, najnovije prve
function vijesti_javne($filtar = [], $koliko = 10, $preskoci = 0)
{
    [$uvjeti, $parametri] = vijesti_javne_uvjeti($filtar);
    $parametri[] = (int) $koliko;
    $parametri[] = (int) $preskoci;

    return db_svi_retci(
        "SELECT v.id, v.naslov, v.sazetak, v.objavljeno_u, v.slika_id,
                v.kategorija_id, v.grad_opcina_id, g.zupanija_id,
                k.naziv AS kategorija, g.naziv AS grad_opcina, z.naziv AS zupanija
         FROM vijesti v
         JOIN kategorije k ON k.id = v.kategorija_id
         LEFT JOIN gradovi_opcine g ON g.id = v.grad_opcina_id
         LEFT JOIN zupanije z ON z.id = g.zupanija_id
         WHERE $uvjeti
         ORDER BY v.objavljeno_u DESC, v.id DESC
         LIMIT ? OFFSET ?",
        $parametri
    );
}

// Jedna javna vijest s kategorijom, područjem i autorom. Neobjavljena ili nepostojeća vraća null.
function vijest_javna_po_id($id)
{
    return db_jedan_redak(
        "SELECT v.id, v.naslov, v.sazetak, v.sadrzaj, v.objavljeno_u, v.slika_id,
                v.kategorija_id, v.grad_opcina_id, g.zupanija_id,
                k.naziv AS kategorija, g.naziv AS grad_opcina, z.naziv AS zupanija, a.ime AS autor
         FROM vijesti v
         JOIN kategorije k ON k.id = v.kategorija_id
         JOIN korisnici a ON a.id = v.autor_id
         LEFT JOIN gradovi_opcine g ON g.id = v.grad_opcina_id
         LEFT JOIN zupanije z ON z.id = g.zupanija_id
         WHERE v.id = ? AND v.status = 'objavljeno' AND v.objavljeno_u <= NOW()",
        [(int) $id]
    );
}

// WHERE uvjet za administraciju: sve vijesti bez obzira na status, uz pretragu po naslovu ili sažetku
function vijesti_admin_uvjeti($trazi, $autor_id)
{
    $uvjeti = ['1 = 1'];
    $parametri = [];

    // Urednik vidi samo svoje vijesti (0 = sve vijesti, za administratora)
    if ($autor_id > 0) {
        $uvjeti[] = 'v.autor_id = ?';
        $parametri[] = (int) $autor_id;
    }

    if ($trazi !== '') {
        $uzorak = uzorak_pretrage($trazi);
        $uvjeti[] = "(v.naslov LIKE ? ESCAPE '|' OR v.sazetak LIKE ? ESCAPE '|')";
        $parametri[] = $uzorak;
        $parametri[] = $uzorak;
    }

    return [implode(' AND ', $uvjeti), $parametri];
}

// Broj vijesti za administraciju (za straničenje)
function vijesti_admin_broj($trazi, $autor_id)
{
    [$uvjeti, $parametri] = vijesti_admin_uvjeti($trazi, $autor_id);

    $redak = db_jedan_redak("SELECT COUNT(*) AS broj FROM vijesti v WHERE $uvjeti", $parametri);

    return (int) $redak['broj'];
}

// Jedna stranica vijesti za administraciju, zadnje izmijenjene prve
function vijesti_admin($trazi, $autor_id, $koliko, $preskoci)
{
    [$uvjeti, $parametri] = vijesti_admin_uvjeti($trazi, $autor_id);
    $parametri[] = (int) $koliko;
    $parametri[] = (int) $preskoci;

    return db_svi_retci(
        "SELECT v.id, v.naslov, v.status, v.objavljeno_u, v.azurirano,
                k.naziv AS kategorija, g.naziv AS grad_opcina, a.ime AS autor
         FROM vijesti v
         JOIN kategorije k ON k.id = v.kategorija_id
         JOIN korisnici a ON a.id = v.autor_id
         LEFT JOIN gradovi_opcine g ON g.id = v.grad_opcina_id
         WHERE $uvjeti
         ORDER BY v.azurirano DESC, v.id DESC
         LIMIT ? OFFSET ?",
        $parametri
    );
}

// Jedna vijest bilo kojeg statusa, za uređivanje. Nepostojeća vraća null.
function vijest_po_id($id)
{
    return db_jedan_redak(
        'SELECT v.id, v.kategorija_id, v.grad_opcina_id, g.zupanija_id, v.autor_id, v.slika_id, v.naslov, v.sazetak, v.sadrzaj,
                v.status, v.objavljeno_u, v.kreirano, v.azurirano, a.ime AS autor,
                k.naziv AS kategorija, g.naziv AS grad_opcina, z.naziv AS zupanija
         FROM vijesti v
         JOIN korisnici a ON a.id = v.autor_id
         JOIN kategorije k ON k.id = v.kategorija_id
         LEFT JOIN gradovi_opcine g ON g.id = v.grad_opcina_id
         LEFT JOIN zupanije z ON z.id = g.zupanija_id
         WHERE v.id = ?',
        [(int) $id]
    );
}

function vijest_dodaj($vijest, $autor_id)
{
    db_izvrsi(
        'INSERT INTO vijesti (kategorija_id, grad_opcina_id, autor_id, slika_id, naslov, sazetak, sadrzaj, status, objavljeno_u)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [$vijest['kategorija_id'], $vijest['grad_opcina_id'], (int) $autor_id, $vijest['slika_id'], $vijest['naslov'],
         $vijest['sazetak'], $vijest['sadrzaj'], $vijest['status'], $vijest['objavljeno_u']]
    );
}

// Uređivanje ne mijenja autora
function vijest_uredi($id, $vijest)
{
    db_izvrsi(
        'UPDATE vijesti
         SET kategorija_id = ?, grad_opcina_id = ?, slika_id = ?, naslov = ?, sazetak = ?, sadrzaj = ?, status = ?, objavljeno_u = ?
         WHERE id = ?',
        [$vijest['kategorija_id'], $vijest['grad_opcina_id'], $vijest['slika_id'], $vijest['naslov'], $vijest['sazetak'],
         $vijest['sadrzaj'], $vijest['status'], $vijest['objavljeno_u'], (int) $id]
    );
}

function vijest_obrisi($id)
{
    db_izvrsi('DELETE FROM vijesti WHERE id = ?', [(int) $id]);
}
