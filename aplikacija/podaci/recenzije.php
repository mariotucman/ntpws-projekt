<?php
// Recenzije lokacija. Javno se vide i u prosjek ulaze samo recenzije koje nisu skrivene.

// Sažetak ocjena lokacije: ['broj' => …, 'prosjek' => … ili null, 'raspodjela' => [5 => broj, 4 => broj, …, 1 => broj]]
function recenzije_sazetak($lokacija_id)
{
    $retci = db_svi_retci(
        'SELECT ocjena, COUNT(*) AS broj FROM recenzije WHERE lokacija_id = ? AND skriveno = 0 GROUP BY ocjena',
        [(int) $lokacija_id]
    );

    $raspodjela = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
    foreach ($retci as $redak) {
        $raspodjela[(int) $redak['ocjena']] = (int) $redak['broj'];
    }

    $broj = array_sum($raspodjela);
    $zbroj = 0;
    foreach ($raspodjela as $ocjena => $koliko) {
        $zbroj += $ocjena * $koliko;
    }

    return ['broj' => $broj, 'prosjek' => $broj > 0 ? $zbroj / $broj : null, 'raspodjela' => $raspodjela];
}

// Jedna stranica javnih recenzija lokacije s imenom autora, najnovije prve
function recenzije_javne($lokacija_id, $koliko, $preskoci)
{
    return db_svi_retci(
        'SELECT r.id, r.ocjena, r.tekst, r.kreirano, k.ime AS autor
         FROM recenzije r
         JOIN korisnici k ON k.id = r.korisnik_id
         WHERE r.lokacija_id = ? AND r.skriveno = 0
         ORDER BY r.kreirano DESC, r.id DESC
         LIMIT ? OFFSET ?',
        [(int) $lokacija_id, (int) $koliko, (int) $preskoci]
    );
}

// Recenzija korisnika za lokaciju, i kad je skrivena (autor uvijek vidi svoju). Bez recenzije vraća null.
function recenzija_korisnika($lokacija_id, $korisnik_id)
{
    return db_jedan_redak(
        'SELECT id, ocjena, tekst, skriveno, kreirano, azurirano FROM recenzije WHERE lokacija_id = ? AND korisnik_id = ?',
        [(int) $lokacija_id, (int) $korisnik_id]
    );
}

// Dodaje recenziju ili mijenja postojeću (jedna po korisniku i lokaciji). Skrivenost se pri uređivanju ne mijenja.
function recenzija_spremi($lokacija_id, $korisnik_id, $ocjena, $tekst)
{
    db_izvrsi(
        'INSERT INTO recenzije (lokacija_id, korisnik_id, ocjena, tekst) VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE ocjena = VALUES(ocjena), tekst = VALUES(tekst)',
        [(int) $lokacija_id, (int) $korisnik_id, (int) $ocjena, $tekst]
    );
}

function recenzija_obrisi($lokacija_id, $korisnik_id)
{
    db_izvrsi('DELETE FROM recenzije WHERE lokacija_id = ? AND korisnik_id = ?', [(int) $lokacija_id, (int) $korisnik_id]);
}

// WHERE uvjet za moderaciju: $status je 'sve', 'vidljive' ili 'skrivene'
function recenzije_admin_uvjeti($status)
{
    return match ($status) {
        'vidljive' => 'r.skriveno = 0',
        'skrivene' => 'r.skriveno = 1',
        default => '1 = 1',
    };
}

// Broj recenzija za moderaciju (za straničenje)
function recenzije_admin_broj($status)
{
    $uvjeti = recenzije_admin_uvjeti($status);
    $redak = db_jedan_redak("SELECT COUNT(*) AS broj FROM recenzije r WHERE $uvjeti");

    return (int) $redak['broj'];
}

// Jedna stranica recenzija za moderaciju s nazivom lokacije i imenom autora, najnovije prve
function recenzije_admin($status, $koliko, $preskoci)
{
    $uvjeti = recenzije_admin_uvjeti($status);

    return db_svi_retci(
        "SELECT r.id, r.ocjena, r.tekst, r.skriveno, r.kreirano, r.lokacija_id,
                l.naziv AS lokacija, l.objavljeno AS lokacija_objavljena, k.ime AS autor
         FROM recenzije r
         JOIN lokacije l ON l.id = r.lokacija_id
         JOIN korisnici k ON k.id = r.korisnik_id
         WHERE $uvjeti
         ORDER BY r.kreirano DESC, r.id DESC
         LIMIT ? OFFSET ?",
        [(int) $koliko, (int) $preskoci]
    );
}

function recenzija_po_id($id)
{
    return db_jedan_redak('SELECT id, lokacija_id, korisnik_id, ocjena, tekst, skriveno FROM recenzije WHERE id = ?', [(int) $id]);
}

function recenzija_postavi_skriveno($id, $skriveno)
{
    db_izvrsi('UPDATE recenzije SET skriveno = ? WHERE id = ?', [$skriveno ? 1 : 0, (int) $id]);
}
