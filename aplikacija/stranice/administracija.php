<?php
// Administracija. Sve stranice su samo za administratora.

// Popis kategorija vijesti
function stranica_admin_kategorije()
{
    zahtijevaj_ulogu(['administrator']);

    prikazi('admin-kategorije', [
        'kategorije' => kategorije_s_brojem_vijesti(),
    ]);
}
