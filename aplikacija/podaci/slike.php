<?php
// Podaci o prenesenim slikama (same datoteke su u mapi prijenosi/)

function slika_po_id($id)
{
    return db_jedan_redak(
        'SELECT id, datoteka, izvorni_naziv, vrsta, velicina, sirina, visina FROM slike WHERE id = ?',
        [(int) $id]
    );
}

// Slika je javna ako pripada barem jednoj objavljenoj vijesti
function slika_javna($id)
{
    $redak = db_jedan_redak(
        "SELECT 1 FROM vijesti WHERE slika_id = ? AND status = 'objavljeno' AND objavljeno_u <= NOW() LIMIT 1",
        [(int) $id]
    );

    return $redak !== null;
}
