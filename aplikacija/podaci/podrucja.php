<?php
// Županije i gradovi/općine (baza/teritorij.sql)

function zupanije_sve()
{
    return db_svi_retci('SELECT id, naziv FROM zupanije ORDER BY id');
}

// Svi gradovi i općine, poredani po županiji pa po nazivu (za padajući izbornik s grupama)
function gradovi_opcine_svi()
{
    return db_svi_retci('SELECT id, zupanija_id, naziv FROM gradovi_opcine ORDER BY zupanija_id, naziv');
}

function grad_opcina_po_id($id)
{
    return db_jedan_redak('SELECT id, zupanija_id, naziv FROM gradovi_opcine WHERE id = ?', [(int) $id]);
}
