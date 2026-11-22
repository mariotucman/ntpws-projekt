<?php
// Događanja i vrste događanja

function vrste_dogadanja_sve()
{
    return db_svi_retci('SELECT id, naziv FROM vrste_dogadanja ORDER BY naziv');
}

// Javno se vide objavljena događanja koja još nisu završila (današnja se vide cijeli dan).
// $filtar: zupanija, grad_opcina, vrsta (id-ovi, 0 = bez filtra), od i do (datumi 'Y-m-d', do može biti null).
// Događanje je u razdoblju ako se s njim preklapa: završava od datuma od, a počinje najkasnije na datum do.
// Vraća WHERE uvjet i parametre, zajedničke za popis i za brojanje.
function dogadanja_javna_uvjeti($filtar)
{
    $uvjeti = 'd.objavljeno = 1 AND COALESCE(d.zavrsetak, d.pocetak) >= ?';
    $parametri = [$filtar['od']];

    if ($filtar['do'] !== null) {
        $uvjeti .= ' AND DATE(d.pocetak) <= ?';
        $parametri[] = $filtar['do'];
    }

    if (($filtar['grad_opcina'] ?? 0) > 0) {
        $uvjeti .= ' AND d.grad_opcina_id = ?';
        $parametri[] = (int) $filtar['grad_opcina'];
    } elseif (($filtar['zupanija'] ?? 0) > 0) {
        $uvjeti .= ' AND g.zupanija_id = ?';
        $parametri[] = (int) $filtar['zupanija'];
    }

    if (($filtar['vrsta'] ?? 0) > 0) {
        $uvjeti .= ' AND d.vrsta_id = ?';
        $parametri[] = (int) $filtar['vrsta'];
    }

    return [$uvjeti, $parametri];
}

// Broj javnih događanja za filtar (za straničenje)
function dogadanja_javna_broj($filtar)
{
    [$uvjeti, $parametri] = dogadanja_javna_uvjeti($filtar);

    $redak = db_jedan_redak(
        "SELECT COUNT(*) AS broj
         FROM dogadanja d
         JOIN gradovi_opcine g ON g.id = d.grad_opcina_id
         WHERE $uvjeti",
        $parametri
    );

    return (int) $redak['broj'];
}

// Jedna stranica javnih događanja za filtar, po danu pa po vremenu početka.
// dan je dan pod kojim se događanje prikazuje: dan početka, a za događanje koje je već počelo prvi dan razdoblja (od).
function dogadanja_javna($filtar, $koliko, $preskoci)
{
    [$uvjeti, $parametri] = dogadanja_javna_uvjeti($filtar);
    array_unshift($parametri, $filtar['od']);
    $parametri[] = (int) $koliko;
    $parametri[] = (int) $preskoci;

    return db_svi_retci(
        "SELECT d.id, d.naziv, d.pocetak, d.zavrsetak, d.cijena, d.adresa, d.na_otvorenom,
                GREATEST(DATE(d.pocetak), ?) AS dan,
                v.naziv AS vrsta, g.naziv AS grad_opcina, l.naziv AS lokacija
         FROM dogadanja d
         JOIN vrste_dogadanja v ON v.id = d.vrsta_id
         JOIN gradovi_opcine g ON g.id = d.grad_opcina_id
         LEFT JOIN lokacije l ON l.id = d.lokacija_id
         WHERE $uvjeti
         ORDER BY dan, TIME(d.pocetak), d.naziv, d.id
         LIMIT ? OFFSET ?",
        $parametri
    );
}

// Jedno javno (objavljeno) događanje s vrstom, područjem i lokacijom. Prošla događanja se također vraćaju.
// lokacija_objavljena govori smije li se na lokaciju postaviti poveznica. Skriveno ili nepostojeće vraća null.
function dogadanje_javno_po_id($id)
{
    return db_jedan_redak(
        'SELECT d.id, d.naziv, d.opis, d.adresa, d.pocetak, d.zavrsetak, d.cijena, d.na_otvorenom, d.slika_id, d.lokacija_id,
                v.naziv AS vrsta, g.naziv AS grad_opcina, z.naziv AS zupanija,
                l.naziv AS lokacija, l.objavljeno AS lokacija_objavljena
         FROM dogadanja d
         JOIN vrste_dogadanja v ON v.id = d.vrsta_id
         JOIN gradovi_opcine g ON g.id = d.grad_opcina_id
         JOIN zupanije z ON z.id = g.zupanija_id
         LEFT JOIN lokacije l ON l.id = d.lokacija_id
         WHERE d.id = ? AND d.objavljeno = 1',
        [(int) $id]
    );
}


// Vrste s brojem događanja svake vrste (za administraciju)
function vrste_dogadanja_s_brojem_dogadanja()
{
    return db_svi_retci(
        'SELECT v.id, v.naziv, COUNT(d.id) AS broj_dogadanja
         FROM vrste_dogadanja v
         LEFT JOIN dogadanja d ON d.vrsta_id = v.id
         GROUP BY v.id, v.naziv
         ORDER BY v.naziv'
    );
}

// Naziv je zauzet ako ga već ima druga vrsta
function vrsta_dogadanja_naziv_zauzet($naziv, $osim_id = 0)
{
    $redak = db_jedan_redak('SELECT id FROM vrste_dogadanja WHERE naziv = ? AND id <> ?', [$naziv, (int) $osim_id]);

    return $redak !== null;
}

function vrsta_dogadanja_po_id($id)
{
    return db_jedan_redak('SELECT id, naziv FROM vrste_dogadanja WHERE id = ?', [(int) $id]);
}

function vrsta_dogadanja_dodaj($naziv)
{
    db_izvrsi('INSERT INTO vrste_dogadanja (naziv) VALUES (?)', [$naziv]);
}

function vrsta_dogadanja_uredi($id, $naziv)
{
    db_izvrsi('UPDATE vrste_dogadanja SET naziv = ? WHERE id = ?', [$naziv, (int) $id]);
}

// Broj događanja te vrste, uključujući skrivena i prošla
function vrsta_dogadanja_broj_dogadanja($id)
{
    $redak = db_jedan_redak('SELECT COUNT(*) AS broj FROM dogadanja WHERE vrsta_id = ?', [(int) $id]);

    return (int) $redak['broj'];
}

// Briše vrstu. Ako je koristi neko događanje, baza to odbija (RESTRICT) i funkcija vraća false.
function vrsta_dogadanja_obrisi($id)
{
    try {
        db_izvrsi('DELETE FROM vrste_dogadanja WHERE id = ?', [(int) $id]);
    } catch (mysqli_sql_exception $greska) {
        if ($greska->getCode() === 1451) {
            return false;
        }
        throw $greska;
    }

    return true;
}

// WHERE uvjet za administraciju: sva događanja (i skrivena i prošla), uz pretragu po nazivu događanja ili grada/općine
function dogadanja_admin_uvjeti($trazi)
{
    if ($trazi === '') {
        return ['1 = 1', []];
    }

    $uzorak = uzorak_pretrage($trazi);

    return ["(d.naziv LIKE ? ESCAPE '|' OR g.naziv LIKE ? ESCAPE '|')", [$uzorak, $uzorak]];
}

// Broj događanja za administraciju (za straničenje)
function dogadanja_admin_broj($trazi)
{
    [$uvjeti, $parametri] = dogadanja_admin_uvjeti($trazi);

    $redak = db_jedan_redak(
        "SELECT COUNT(*) AS broj
         FROM dogadanja d
         JOIN gradovi_opcine g ON g.id = d.grad_opcina_id
         WHERE $uvjeti",
        $parametri
    );

    return (int) $redak['broj'];
}

// Jedna stranica događanja za administraciju, po početku od najkasnijeg
function dogadanja_admin($trazi, $koliko, $preskoci)
{
    [$uvjeti, $parametri] = dogadanja_admin_uvjeti($trazi);
    $parametri[] = (int) $koliko;
    $parametri[] = (int) $preskoci;

    return db_svi_retci(
        "SELECT d.id, d.naziv, d.pocetak, d.zavrsetak, d.objavljeno, d.istaknuto,
                v.naziv AS vrsta, g.naziv AS grad_opcina, a.ime AS autor
         FROM dogadanja d
         JOIN vrste_dogadanja v ON v.id = d.vrsta_id
         JOIN gradovi_opcine g ON g.id = d.grad_opcina_id
         JOIN korisnici a ON a.id = d.autor_id
         WHERE $uvjeti
         ORDER BY d.pocetak DESC, d.id DESC
         LIMIT ? OFFSET ?",
        $parametri
    );
}

// Jedno događanje bilo kojeg statusa, za uređivanje. Nepostojeće vraća null.
function dogadanje_po_id($id)
{
    return db_jedan_redak(
        'SELECT d.id, d.vrsta_id, d.grad_opcina_id, g.zupanija_id, d.lokacija_id, d.autor_id, d.slika_id, d.naziv, d.opis,
                d.adresa, d.pocetak, d.zavrsetak, d.cijena, d.na_otvorenom, d.objavljeno, d.istaknuto, d.kreirano, d.azurirano,
                a.ime AS autor
         FROM dogadanja d
         JOIN gradovi_opcine g ON g.id = d.grad_opcina_id
         JOIN korisnici a ON a.id = d.autor_id
         WHERE d.id = ?',
        [(int) $id]
    );
}

// Dodaje događanje i vraća njegov id
function dogadanje_dodaj($dogadanje, $autor_id)
{
    db_izvrsi(
        'INSERT INTO dogadanja (vrsta_id, grad_opcina_id, lokacija_id, autor_id, slika_id, naziv, opis, adresa, pocetak, zavrsetak,
                                cijena, na_otvorenom, objavljeno, istaknuto)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
        [$dogadanje['vrsta_id'], $dogadanje['grad_opcina_id'], $dogadanje['lokacija_id'], (int) $autor_id, $dogadanje['slika_id'],
         $dogadanje['naziv'], $dogadanje['opis'], $dogadanje['adresa'], $dogadanje['pocetak'], $dogadanje['zavrsetak'],
         $dogadanje['cijena'], $dogadanje['na_otvorenom'], $dogadanje['objavljeno'], $dogadanje['istaknuto']]
    );

    return mysqli_insert_id(db());
}

// Uređivanje ne mijenja autora
function dogadanje_uredi($id, $dogadanje)
{
    db_izvrsi(
        'UPDATE dogadanja
         SET vrsta_id = ?, grad_opcina_id = ?, lokacija_id = ?, slika_id = ?, naziv = ?, opis = ?, adresa = ?, pocetak = ?,
             zavrsetak = ?, cijena = ?, na_otvorenom = ?, objavljeno = ?, istaknuto = ?
         WHERE id = ?',
        [$dogadanje['vrsta_id'], $dogadanje['grad_opcina_id'], $dogadanje['lokacija_id'], $dogadanje['slika_id'],
         $dogadanje['naziv'], $dogadanje['opis'], $dogadanje['adresa'], $dogadanje['pocetak'], $dogadanje['zavrsetak'],
         $dogadanje['cijena'], $dogadanje['na_otvorenom'], $dogadanje['objavljeno'], $dogadanje['istaknuto'], (int) $id]
    );
}

// Briše događanje. Sliku događanja briše pozivatelj.
function dogadanje_obrisi($id)
{
    db_izvrsi('DELETE FROM dogadanja WHERE id = ?', [(int) $id]);
}
