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

// Plan zadanog korisnika. Tuđi ili nepostojeći plan vraća null (stranica tada daje 404).
function plan_korisnika($id, $korisnik_id)
{
    return db_jedan_redak(
        'SELECT id, naziv, datum_od, datum_do FROM planovi WHERE id = ? AND korisnik_id = ?',
        [(int) $id, (int) $korisnik_id]
    );
}

// Stavke plana poredane po danu i vremenu (stavke bez vremena na kraju dana).
// Stavka je lokacija ili događanje; prikazuju se samo objavljene. slika_id je prva fotografija lokacije ili slika događanja.
function stavke_plana($plan_id)
{
    return db_svi_retci(
        'SELECT s.id, s.dan, s.vrijeme, s.lokacija_id, s.dogadanje_id,
                COALESCE(l.naziv, d.naziv) AS naziv,
                COALESCE(vl.naziv, vd.naziv) AS vrsta,
                g.id AS grad_opcina_id, g.naziv AS grad_opcina,
                COALESCE(
                    (SELECT f.slika_id FROM fotografije_lokacija f WHERE f.lokacija_id = l.id ORDER BY f.redoslijed, f.slika_id LIMIT 1),
                    d.slika_id
                ) AS slika_id
         FROM stavke_plana s
         LEFT JOIN lokacije l ON l.id = s.lokacija_id AND l.objavljeno = 1
         LEFT JOIN vrste_lokacija vl ON vl.id = l.vrsta_id
         LEFT JOIN dogadanja d ON d.id = s.dogadanje_id AND d.objavljeno = 1
         LEFT JOIN vrste_dogadanja vd ON vd.id = d.vrsta_id
         JOIN gradovi_opcine g ON g.id = COALESCE(l.grad_opcina_id, d.grad_opcina_id)
         WHERE s.plan_id = ?
         ORDER BY s.dan, s.vrijeme IS NULL, s.vrijeme, s.id',
        [(int) $plan_id]
    );
}

// Broj stavki plana izvan razdoblja od–do (one bi se obrisale promjenom datuma)
function plan_broj_stavki_izvan($plan_id, $datum_od, $datum_do)
{
    $redak = db_jedan_redak(
        'SELECT COUNT(*) AS broj FROM stavke_plana WHERE plan_id = ? AND (dan < ? OR dan > ?)',
        [(int) $plan_id, $datum_od, $datum_do]
    );

    return (int) $redak['broj'];
}

// Mijenja naziv i razdoblje plana i briše stavke s dana koji su ispali iz razdoblja, u jednoj transakciji.
// Vraća broj obrisanih stavki.
function plan_uredi($id, $naziv, $datum_od, $datum_do)
{
    mysqli_begin_transaction(db());

    try {
        db_izvrsi(
            'UPDATE planovi SET naziv = ?, datum_od = ?, datum_do = ? WHERE id = ?',
            [$naziv, $datum_od, $datum_do, (int) $id]
        );
        $obrisano = db_izvrsi(
            'DELETE FROM stavke_plana WHERE plan_id = ? AND (dan < ? OR dan > ?)',
            [(int) $id, $datum_od, $datum_do]
        );
        mysqli_commit(db());
    } catch (Throwable $greska) {
        mysqli_rollback(db());
        throw $greska;
    }

    return $obrisano;
}

// Briše plan, a s njim i sve njegove stavke (ON DELETE CASCADE)
function plan_obrisi($id)
{
    db_izvrsi('DELETE FROM planovi WHERE id = ?', [(int) $id]);
}

// Id-ovi lokacija i događanja koji su već u planu na zadani dan: ['lokacije' => [...], 'dogadanja' => [...]]
function stavke_dana($plan_id, $dan)
{
    $retci = db_svi_retci(
        'SELECT lokacija_id, dogadanje_id FROM stavke_plana WHERE plan_id = ? AND dan = ?',
        [(int) $plan_id, $dan]
    );

    return [
        'lokacije' => array_map('intval', array_filter(array_column($retci, 'lokacija_id'))),
        'dogadanja' => array_map('intval', array_filter(array_column($retci, 'dogadanje_id'))),
    ];
}

// Dodaje lokaciju ili događanje (drugo je null) na dan plana.
// Ako je ta lokacija ili događanje već u planu taj dan, baza to odbija (UNIQUE) i funkcija vraća false.
function stavka_dodaj($plan_id, $dan, $vrijeme, $lokacija_id, $dogadanje_id)
{
    try {
        db_izvrsi(
            'INSERT INTO stavke_plana (plan_id, dan, vrijeme, lokacija_id, dogadanje_id) VALUES (?, ?, ?, ?, ?)',
            [(int) $plan_id, $dan, $vrijeme, $lokacija_id, $dogadanje_id]
        );
    } catch (mysqli_sql_exception $greska) {
        if ($greska->getCode() === 1062) {
            return false;
        }
        throw $greska;
    }

    return true;
}
