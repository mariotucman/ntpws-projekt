<?php

function korisnik_email_zauzet($email)
{
    $redak = db_jedan_redak('SELECT id FROM korisnici WHERE email = ?', [$email]);

    return $redak !== null;
}

// Novi korisnik uvijek dobiva ulogu Korisnik, a lozinka se sprema samo kao hash
function korisnik_dodaj($ime, $email, $lozinka)
{
    db_izvrsi(
        "INSERT INTO korisnici (uloga_id, ime, email, lozinka_hash)
         SELECT id, ?, ?, ? FROM uloge WHERE oznaka = 'korisnik'",
        [$ime, $email, password_hash($lozinka, PASSWORD_DEFAULT)]
    );
}

function korisnik_po_emailu($email)
{
    return db_jedan_redak('SELECT id, ime, lozinka_hash, aktivan FROM korisnici WHERE email = ?', [$email]);
}

function korisnik_po_id($id)
{
    return db_jedan_redak(
        'SELECT k.id, k.ime, k.email, k.aktivan, u.oznaka AS uloga
         FROM korisnici k
         JOIN uloge u ON u.id = k.uloga_id
         WHERE k.id = ?',
        [(int) $id]
    );
}
