<?php

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
        $uzorak = '%' . str_replace(['|', '%', '_'], ['||', '|%', '|_'], $filtar['trazi']) . '%';
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
        "SELECT v.id, v.naslov, v.sazetak, v.objavljeno_u,
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
        "SELECT v.id, v.naslov, v.sazetak, v.sadrzaj, v.objavljeno_u,
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
