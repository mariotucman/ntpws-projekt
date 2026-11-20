<?php
// Podaci o prenesenim slikama (same datoteke su u mapi prijenosi/)

function slika_po_id($id)
{
    return db_jedan_redak(
        'SELECT id, datoteka, izvorni_naziv, vrsta, velicina, sirina, visina FROM slike WHERE id = ?',
        [(int) $id]
    );
}

// Slika je javna ako pripada objavljenoj vijesti ili je fotografija objavljene lokacije
function slika_javna($id)
{
    $redak = db_jedan_redak(
        "SELECT 1 FROM vijesti WHERE slika_id = ? AND status = 'objavljeno' AND objavljeno_u <= NOW()
         UNION ALL
         SELECT 1 FROM fotografije_lokacija f JOIN lokacije l ON l.id = f.lokacija_id WHERE f.slika_id = ? AND l.objavljeno = 1
         LIMIT 1",
        [(int) $id, (int) $id]
    );

    return $redak !== null;
}

// Upisuje podatke o prenesenoj slici i vraća njezin id
function slika_dodaj($slika, $prenio_id)
{
    db_izvrsi(
        'INSERT INTO slike (datoteka, izvorni_naziv, vrsta, velicina, sirina, visina, prenio_id) VALUES (?, ?, ?, ?, ?, ?, ?)',
        [$slika['datoteka'], $slika['izvorni_naziv'], $slika['vrsta'], $slika['velicina'], $slika['sirina'], $slika['visina'], (int) $prenio_id]
    );

    return mysqli_insert_id(db());
}

// Briše podatke o slici (datoteku briše obrisi_datoteku_slike())
function slika_obrisi($id)
{
    db_izvrsi('DELETE FROM slike WHERE id = ?', [(int) $id]);
}
