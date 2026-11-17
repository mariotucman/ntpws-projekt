<?php

function kategorije_sve()
{
    return db_svi_retci('SELECT id, naziv FROM kategorije ORDER BY naziv');
}

// Kategorije s brojem vijesti u svakoj (za administraciju)
function kategorije_s_brojem_vijesti()
{
    return db_svi_retci(
        'SELECT k.id, k.naziv, COUNT(v.id) AS broj_vijesti
         FROM kategorije k
         LEFT JOIN vijesti v ON v.kategorija_id = k.id
         GROUP BY k.id, k.naziv
         ORDER BY k.naziv'
    );
}
