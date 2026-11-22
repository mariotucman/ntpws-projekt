<?php

const DOGADANJA_PO_STRANICI = 10;

// Datum iz adrese u obliku Y-m-d (polje type="date"). Prazan ili neispravan datum vraća null.
function get_datum($kljuc)
{
    $tekst = get_tekst($kljuc, 10);
    $datum = $tekst !== '' ? date_create_from_format('!Y-m-d', $tekst) : false;

    if ($datum === false || date_get_last_errors() !== false) {
        return null;
    }

    return date_format($datum, 'Y-m-d');
}

// Popis nadolazećih događanja s filtrom županije, grada/općine, vrste i razdoblja, grupiran po danima
function stranica_dogadanja()
{
    $zupanije = zupanije_sve();
    $vrste = vrste_dogadanja_sve();
    [$zupanija_id, $grad_opcina_id] = odabrano_podrucje($zupanije);

    $vrsta_id = get_broj('vrsta');
    if (!in_array($vrsta_id, array_map('intval', array_column($vrste, 'id')), true)) {
        $vrsta_id = 0;
    }

    // Razdoblje: od ne može biti prije danas (prošla događanja se ne prikazuju), a do ne može biti prije od
    $danas = date('Y-m-d');
    $od = get_datum('od');
    $do = get_datum('do');
    $od_filtra = $od !== null && $od > $danas ? $od : $danas;
    if ($do !== null && $do < $od_filtra) {
        $do = $od_filtra;
    }

    $filtar = [
        'zupanija' => $zupanija_id,
        'grad_opcina' => $grad_opcina_id,
        'vrsta' => $vrsta_id,
        'od' => $od_filtra,
        'do' => $do,
    ];

    // Straničenje: ?str=2. Broj veći od zadnje stranice prikazuje zadnju stranicu.
    $ukupno = dogadanja_javna_broj($filtar);
    $broj_stranica = max(1, (int) ceil($ukupno / DOGADANJA_PO_STRANICI));
    $trenutna_stranica = min(max(1, get_broj('str')), $broj_stranica);

    // Parametri adrese bez stranice, da poveznice straničenja zadrže filtar
    $adresa = array_filter([
        'stranica' => 'dogadanja',
        'zupanija' => $zupanija_id,
        'grad_opcina' => $grad_opcina_id,
        'vrsta' => $vrsta_id,
        'od' => $od !== null && $od > $danas ? $od : '',
        'do' => $do ?? '',
    ]);

    prikazi('dogadanja', [
        'dogadanja' => dogadanja_javna($filtar, DOGADANJA_PO_STRANICI, ($trenutna_stranica - 1) * DOGADANJA_PO_STRANICI),
        'trenutna_stranica' => $trenutna_stranica,
        'broj_stranica' => $broj_stranica,
        'adresa' => $adresa,
        'zupanije' => $zupanije,
        'gradovi_opcine' => gradovi_opcine_svi(),
        'vrste' => $vrste,
        'odabrana_zupanija' => $zupanija_id,
        'odabrani_grad_opcina' => $grad_opcina_id,
        'odabrana_vrsta' => $vrsta_id,
        'od' => $adresa['od'] ?? '',
        'do' => $do ?? '',
    ]);
}

// Detalj događanja: ?stranica=dogadanje&id=5. Skriveno ili nepostojeće daje 404, a prošlo se prikazuje uz napomenu.
function stranica_dogadanje()
{
    $dogadanje = dogadanje_javno_po_id(get_broj('id'));

    if ($dogadanje === null) {
        stranica_nije_pronadena();
        return;
    }

    // Završilo je kad je prošao završetak, a događanje bez završetka kad je prošao dan početka
    $kraj = $dogadanje['zavrsetak'] ?? date('Y-m-d 23:59:59', strtotime($dogadanje['pocetak']));

    prikazi('dogadanje', [
        'dogadanje' => $dogadanje,
        'zavrsilo' => strtotime($kraj) < time(),
        'slika' => $dogadanje['slika_id'] !== null ? slika_po_id($dogadanje['slika_id']) : null,
    ]);
}

