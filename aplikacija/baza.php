<?php

// Greške baze bacaju error.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function db()
{
    static $veza = null;

    if ($veza === null) {
        $veza = mysqli_connect(BAZA_POSLUZITELJ, BAZA_KORISNIK, BAZA_LOZINKA, BAZA_NAZIV, BAZA_PORT);
        mysqli_set_charset($veza, 'utf8mb4');
    }

    return $veza;
}

// Priprema i izvršava upit.
function db_upit($sql, $parametri = [])
{
    $naredba = mysqli_prepare(db(), $sql);

    if (count($parametri) > 0) {
        $tipovi = '';
        foreach ($parametri as $vrijednost) {
            if (is_int($vrijednost)) {
                $tipovi .= 'i';
            } elseif (is_float($vrijednost)) {
                $tipovi .= 'd';
            } else {
                $tipovi .= 's';
            }
        }
        mysqli_stmt_bind_param($naredba, $tipovi, ...$parametri);
    }

    mysqli_stmt_execute($naredba);

    return $naredba;
}

// Vraća sve retke rezultata.
function db_svi_retci($sql, $parametri = [])
{
    $naredba = db_upit($sql, $parametri);
    $rezultat = mysqli_stmt_get_result($naredba);
    $retci = mysqli_fetch_all($rezultat, MYSQLI_ASSOC);
    mysqli_stmt_close($naredba);

    return $retci;
}

// Vraća prvi redak rezultata ili null ako nema rezultata.
function db_jedan_redak($sql, $parametri = [])
{
    $naredba = db_upit($sql, $parametri);
    $rezultat = mysqli_stmt_get_result($naredba);
    $redak = mysqli_fetch_assoc($rezultat);
    mysqli_stmt_close($naredba);

    return $redak ?: null;
}
