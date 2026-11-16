<?php

// Grad/općina i županija mogu biti NULL (vijest za cijelu Hrvatsku)
// Bez filtra se vide sve vijesti
function vijesti_javne($zupanija_id = 0, $grad_opcina_id = 0)
{
    $uvjeti = "v.status = 'objavljeno' AND v.objavljeno_u <= NOW()";
    $parametri = [];

    if ($grad_opcina_id > 0) {
        $uvjeti .= ' AND v.grad_opcina_id = ?';
        $parametri[] = (int) $grad_opcina_id;
    } elseif ($zupanija_id > 0) {
        $uvjeti .= ' AND g.zupanija_id = ?';
        $parametri[] = (int) $zupanija_id;
    }

    return db_svi_retci(
        "SELECT v.id, v.naslov, v.sazetak, v.objavljeno_u,
                k.naziv AS kategorija, g.naziv AS grad_opcina, z.naziv AS zupanija
         FROM vijesti v
         JOIN kategorije k ON k.id = v.kategorija_id
         LEFT JOIN gradovi_opcine g ON g.id = v.grad_opcina_id
         LEFT JOIN zupanije z ON z.id = g.zupanija_id
         WHERE $uvjeti
         ORDER BY v.objavljeno_u DESC, v.id DESC",
        $parametri
    );
}
