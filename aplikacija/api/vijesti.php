<?php

// Vijest iz baze u obliku za API. Detalj ima i sadržaj (HTML, očišćen pri spremanju) i autora.
function api_vijest($vijest, $detalj = false)
{
    $podaci = [
        'id' => $vijest['id'],
        'naslov' => $vijest['naslov'],
        'sazetak' => $vijest['sazetak'],
        'objavljeno_u' => api_datum($vijest['objavljeno_u']),
        'kategorija' => ['id' => $vijest['kategorija_id'], 'naziv' => $vijest['kategorija']],
        // Vijest bez grada/općine vrijedi za cijelu Hrvatsku
        'grad_opcina' => $vijest['grad_opcina_id'] === null ? null
            : ['id' => $vijest['grad_opcina_id'], 'naziv' => $vijest['grad_opcina']],
        'zupanija' => $vijest['zupanija_id'] === null ? null
            : ['id' => $vijest['zupanija_id'], 'naziv' => $vijest['zupanija']],
        'slika' => api_adresa(adresa_slike_vijesti($vijest['slika_id'])),
    ];

    if ($detalj) {
        $podaci['sadrzaj'] = $vijest['sadrzaj'];
        $podaci['autor'] = $vijest['autor'];
    }

    return $podaci;
}

// GET /v1/vijesti — objavljene vijesti, najnovije prve.
// Filtri: kategorija, zupanija, grad_opcina (id-ovi; grad/općina ima prednost pred županijom) i trazi (naslov ili sažetak).
function api_vijesti_popis()
{
    $greske = [];
    [$stranica, $po_stranici] = api_stranicenje($greske);
    $filtar = [
        'kategorija' => api_get_broj('kategorija', $greske),
        'zupanija' => api_get_broj('zupanija', $greske),
        'grad_opcina' => api_get_broj('grad_opcina', $greske),
        'trazi' => api_get_tekst('trazi', $greske),
    ];

    if (count($greske) > 0) {
        api_greska(400, 'Neispravni parametri upita.', $greske);
    }

    $ukupno = vijesti_javne_broj($filtar);
    $vijesti = vijesti_javne($filtar, $po_stranici, ($stranica - 1) * $po_stranici);

    api_odgovor(
        array_map('api_vijest', $vijesti),
        200,
        ['stranicenje' => api_podaci_stranicenja($stranica, $po_stranici, $ukupno)]
    );
}

// GET /v1/vijesti/{id} — neobjavljena ili nepostojeća vijest daje 404
function api_vijest_detalj($id)
{
    $vijest = vijest_javna_po_id($id);

    if ($vijest === null) {
        api_greska(404, 'Vijest ne postoji.');
    }

    api_odgovor(api_vijest($vijest, true));
}
