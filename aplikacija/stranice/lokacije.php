<?php

const LOKACIJE_PO_STRANICI = 6;

// Popis lokacija s filtrom županije, grada/općine i vrste, pretragom i straničenjem
function stranica_lokacije()
{
    $zupanije = zupanije_sve();
    $vrste = vrste_lokacija_sve();
    [$zupanija_id, $grad_opcina_id] = odabrano_podrucje($zupanije);

    $vrsta_id = get_broj('vrsta');
    if (!in_array($vrsta_id, array_map('intval', array_column($vrste, 'id')), true)) {
        $vrsta_id = 0;
    }

    $filtar = [
        'zupanija' => $zupanija_id,
        'grad_opcina' => $grad_opcina_id,
        'vrsta' => $vrsta_id,
        'trazi' => get_tekst('trazi'),
    ];

    // Straničenje: ?str=2. Broj veći od zadnje stranice prikazuje zadnju stranicu.
    $ukupno = lokacije_javne_broj($filtar);
    $broj_stranica = max(1, (int) ceil($ukupno / LOKACIJE_PO_STRANICI));
    $trenutna_stranica = min(max(1, get_broj('str')), $broj_stranica);

    // Parametri adrese bez stranice, da poveznice straničenja zadrže filtar
    $adresa = array_filter([
        'stranica' => 'lokacije',
        'trazi' => $filtar['trazi'],
        'zupanija' => $zupanija_id,
        'grad_opcina' => $grad_opcina_id,
        'vrsta' => $vrsta_id,
    ]);

    prikazi('lokacije', [
        'lokacije' => lokacije_javne($filtar, LOKACIJE_PO_STRANICI, ($trenutna_stranica - 1) * LOKACIJE_PO_STRANICI),
        'trenutna_stranica' => $trenutna_stranica,
        'broj_stranica' => $broj_stranica,
        'adresa' => $adresa,
        'zupanije' => $zupanije,
        'gradovi_opcine' => gradovi_opcine_svi(),
        'vrste' => $vrste,
        'odabrana_zupanija' => $zupanija_id,
        'odabrani_grad_opcina' => $grad_opcina_id,
        'odabrana_vrsta' => $vrsta_id,
        'trazi' => $filtar['trazi'],
    ]);
}

// Detalj lokacije: ?stranica=lokacija&id=5. Skrivena ili nepostojeća lokacija daje 404.
function stranica_lokacija()
{
    $lokacija = lokacija_javna_po_id(get_broj('id'));

    if ($lokacija === null) {
        stranica_nije_pronadena();
        return;
    }

    prikazi('lokacija', [
        'lokacija' => $lokacija,
        'radno_vrijeme' => radno_vrijeme_za_prikaz($lokacija['radno_vrijeme']),
    ]);
}
