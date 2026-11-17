<?php

const VIJESTI_PO_STRANICI = 4;

// Odabir županije i grada/općine
// Ako je odabran samo grad, županija se postavlja prema njemu
function odabrano_podrucje($zupanije)
{
    $zupanija_id = get_broj('zupanija');
    $grad_opcina_id = get_broj('grad_opcina');

    if (!in_array($zupanija_id, array_map('intval', array_column($zupanije, 'id')), true)) {
        $zupanija_id = 0;
    }

    if ($grad_opcina_id > 0) {
        $grad_opcina = grad_opcina_po_id($grad_opcina_id);

        if ($grad_opcina === null || ($zupanija_id > 0 && (int) $grad_opcina['zupanija_id'] !== $zupanija_id)) {
            $grad_opcina_id = 0;
        } else {
            $zupanija_id = (int) $grad_opcina['zupanija_id'];
        }
    }

    return [$zupanija_id, $grad_opcina_id];
}

function stranica_vijesti()
{
    $zupanije = zupanije_sve();
    $kategorije = kategorije_sve();
    [$zupanija_id, $grad_opcina_id] = odabrano_podrucje($zupanije);

    $kategorija_id = get_broj('kategorija');
    if (!in_array($kategorija_id, array_map('intval', array_column($kategorije, 'id')), true)) {
        $kategorija_id = 0;
    }

    $filtar = [
        'zupanija' => $zupanija_id,
        'grad_opcina' => $grad_opcina_id,
        'kategorija' => $kategorija_id,
        'trazi' => get_tekst('trazi'),
    ];

    // Straničenje: ?str=2. Broj veći od zadnje stranice prikazuje zadnju stranicu.
    $ukupno = vijesti_javne_broj($filtar);
    $broj_stranica = max(1, (int) ceil($ukupno / VIJESTI_PO_STRANICI));
    $trenutna_stranica = min(max(1, get_broj('str')), $broj_stranica);

    // Parametri adrese bez stranice, da poveznice straničenja zadrže filtar
    $adresa = array_filter([
        'stranica' => 'vijesti',
        'trazi' => $filtar['trazi'],
        'zupanija' => $zupanija_id,
        'grad_opcina' => $grad_opcina_id,
        'kategorija' => $kategorija_id,
    ]);

    prikazi('vijesti', [
        'vijesti' => vijesti_javne($filtar, VIJESTI_PO_STRANICI, ($trenutna_stranica - 1) * VIJESTI_PO_STRANICI),
        'trenutna_stranica' => $trenutna_stranica,
        'broj_stranica' => $broj_stranica,
        'adresa' => $adresa,
        'zupanije' => $zupanije,
        'gradovi_opcine' => gradovi_opcine_svi(),
        'kategorije' => $kategorije,
        'odabrana_zupanija' => $zupanija_id,
        'odabrani_grad_opcina' => $grad_opcina_id,
        'odabrana_kategorija' => $kategorija_id,
        'trazi' => $filtar['trazi'],
    ]);
}

// Detalj vijesti: ?stranica=vijest&id=5. Neobjavljena ili nepostojeća vijest daje 404.
function stranica_vijest()
{
    $vijest = vijest_javna_po_id(get_broj('id'));

    if ($vijest === null) {
        stranica_nije_pronadena();
        return;
    }

    prikazi('vijest', [
        'vijest' => $vijest,
    ]);
}
