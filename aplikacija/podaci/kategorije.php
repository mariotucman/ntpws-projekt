<?php

function kategorije_sve()
{
    return db_svi_retci('SELECT id, naziv FROM kategorije ORDER BY naziv');
}

// Kategorije s brojem vijesti u svakoj (za administraciju)
function kategorije_s_brojem_vijesti()
{
    return db_svi_retci(
        'SELECT k.id, k.naziv, COUNT(v.id) AS broj_vijesti
         FROM kategorije k
         LEFT JOIN vijesti v ON v.kategorija_id = k.id
         GROUP BY k.id, k.naziv
         ORDER BY broj_vijesti DESC, k.naziv'
    );
}

// Naziv je zauzet ako ga već ima druga kategorija
function kategorija_naziv_zauzet($naziv, $osim_id = 0)
{
    $redak = db_jedan_redak('SELECT id FROM kategorije WHERE naziv = ? AND id <> ?', [$naziv, (int) $osim_id]);

    return $redak !== null;
}

function kategorija_dodaj($naziv)
{
    db_izvrsi('INSERT INTO kategorije (naziv) VALUES (?)', [$naziv]);
}

function kategorija_po_id($id)
{
    return db_jedan_redak('SELECT id, naziv FROM kategorije WHERE id = ?', [(int) $id]);
}

function kategorija_uredi($id, $naziv)
{
    db_izvrsi('UPDATE kategorije SET naziv = ? WHERE id = ?', [$naziv, (int) $id]);
}

// Broj vijesti u kategoriji
function kategorija_broj_vijesti($id)
{
    $redak = db_jedan_redak('SELECT COUNT(*) AS broj FROM vijesti WHERE kategorija_id = ?', [(int) $id]);

    return (int) $redak['broj'];
}

// Briše kategoriju. Ako je koristi neka vijest, baza to odbija (RESTRICT) i funkcija vraća false.
function kategorija_obrisi($id)
{
    try {
        db_izvrsi('DELETE FROM kategorije WHERE id = ?', [(int) $id]);
    } catch (mysqli_sql_exception $greska) {
        if ($greska->getCode() === 1451) {
            return false;
        }
        throw $greska;
    }

    return true;
}
