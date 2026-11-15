<?php

// Kod uređivanja profila vlastiti email se ne računa kao zauzet ($osim_id)
function korisnik_email_zauzet($email, $osim_id = 0)
{
    $redak = db_jedan_redak('SELECT id FROM korisnici WHERE email = ? AND id <> ?', [$email, (int) $osim_id]);

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

function korisnik_uredi_profil($id, $ime, $email)
{
    db_izvrsi('UPDATE korisnici SET ime = ?, email = ? WHERE id = ?', [$ime, $email, (int) $id]);
}

function korisnik_lozinka_hash($id)
{
    $redak = db_jedan_redak('SELECT lozinka_hash FROM korisnici WHERE id = ?', [(int) $id]);

    return $redak['lozinka_hash'] ?? '';
}

function korisnik_promijeni_lozinku($id, $lozinka)
{
    db_izvrsi('UPDATE korisnici SET lozinka_hash = ? WHERE id = ?', [password_hash($lozinka, PASSWORD_DEFAULT), (int) $id]);
}

function korisnik_po_id($id)
{
    return db_jedan_redak(
        'SELECT k.id, k.ime, k.email, k.aktivan, k.kreirano, u.oznaka AS uloga, u.naziv AS uloga_naziv
         FROM korisnici k
         JOIN uloge u ON u.id = k.uloga_id
         WHERE k.id = ?',
        [(int) $id]
    );
}
