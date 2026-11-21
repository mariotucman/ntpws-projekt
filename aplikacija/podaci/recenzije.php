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
