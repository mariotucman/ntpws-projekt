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
