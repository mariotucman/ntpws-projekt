<?php
// Planovi putovanja („Moj plan”) i njihove stavke

// Planovi korisnika s brojem stavki: prvo nadolazeći i oni u tijeku (po početku), a prošli na kraju
function planovi_korisnika($korisnik_id)
{
    return db_svi_retci(
        'SELECT p.id, p.naziv, p.datum_od, p.datum_do, COUNT(s.id) AS broj_stavki
         FROM planovi p
         LEFT JOIN stavke_plana s ON s.plan_id = p.id
         WHERE p.korisnik_id = ?
         GROUP BY p.id
         ORDER BY p.datum_do < CURDATE(), p.datum_od, p.id',
        [(int) $korisnik_id]
    );
}

// Novi plan, vraća njegov id
function plan_dodaj($korisnik_id, $naziv, $datum_od, $datum_do)
{
    db_izvrsi(
        'INSERT INTO planovi (korisnik_id, naziv, datum_od, datum_do) VALUES (?, ?, ?, ?)',
        [(int) $korisnik_id, $naziv, $datum_od, $datum_do]
    );

    return mysqli_insert_id(db());
}
