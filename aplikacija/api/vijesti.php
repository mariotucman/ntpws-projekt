<?php

// Vijest iz baze u obliku za API. Detalj ima i sadržaj (HTML, očišćen pri spremanju) i autora,
// a urednički prikaz (vijest bilo kojeg statusa) i status te vrijeme izrade i zadnje izmjene.
function api_vijest($vijest, $detalj = false, $urednicki = false)
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

    if ($urednicki) {
        $podaci['status'] = $vijest['status'];
        $podaci['kreirano'] = api_datum($vijest['kreirano']);
        $podaci['azurirano'] = api_datum($vijest['azurirano']);
    }

    return $podaci;
}

// Isto pravilo kao smije_urediti_vijest() na stranici: administrator sve vijesti, urednik samo svoje
function api_smije_urediti_vijest($korisnik, $vijest)
{
    return $korisnik['uloga'] === 'administrator'
        || ($korisnik['uloga'] === 'urednik' && (int) $vijest['autor_id'] === (int) $korisnik['id']);
}

// Vijest koju prijavljeni korisnik smije mijenjati. Nepostojeća daje 404, a tuđa (za urednika) 403.
function api_vijest_za_izmjenu($id)
{
    $korisnik = api_zahtijevaj_ulogu(['administrator', 'urednik']);
    $vijest = vijest_po_id($id);

    if ($vijest === null) {
        api_greska(404, 'Vijest ne postoji.');
    }
    if (!api_smije_urediti_vijest($korisnik, $vijest)) {
        api_greska(403, 'Možeš mijenjati samo svoje vijesti.');
    }

    return $vijest;
}

// Sadržaj iz API-ja: običan tekst (bez oznaka) pretvara se u odlomke, a HTML se čisti kao iz Quilla
function api_sadrzaj_vijesti($tekst)
{
    if (!str_contains($tekst, '<')) {
        $tekst = tekst_u_odlomke($tekst);
    }

    return ocisti_html($tekst);
}

// Datum objave za bazu. Nacrt nema datum objave, a ostali statusi bez datuma dobivaju trenutno vrijeme.
// $poslano je vrijednost iz tijela (RFC 3339, već provjerena shemom) ili null.
function api_datum_objave($poslano, $status)
{
    if ($status === 'nacrt') {
        return null;
    }

    return $poslano === null ? date('Y-m-d H:i:s') : date('Y-m-d H:i:s', strtotime($poslano));
}

// Provjera koju shema ne može napraviti (postoje li kategorija i grad, sadržaj nakon čišćenja, datum povučene vijesti).
// Ključevi grešaka su nazivi polja API-ja.
function api_provjeri_vijest($vijest)
{
    $greske = provjeri_vijest($vijest, 0);

    if (isset($greske['kategorija'])) {
        $greske['kategorija_id'] = 'Kategorija ne postoji.';
        unset($greske['kategorija']);
    }
    if (isset($greske['grad_opcina'])) {
        $greske['grad_opcina_id'] = 'Grad ili općina ne postoji.';
        unset($greske['grad_opcina']);
    }

    if (count($greske) > 0) {
        api_greska(400, 'Neispravan unos.', $greske);
    }
}

// Odgovor s vijesti kako je sada u bazi (urednički prikaz)
function api_odgovor_vijest($id, $status)
{
    api_odgovor(api_vijest(vijest_po_id($id), true, true), $status);
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

// GET /v1/vijesti/{id} — objavljena vijest. Neobjavljenu vidi samo onaj tko je smije mijenjati
// (s tokenom), a svi ostali dobivaju 404, kao da ne postoji.
function api_vijest_detalj($id)
{
    $vijest = vijest_javna_po_id($id);
    if ($vijest !== null) {
        api_odgovor(api_vijest($vijest, true));
    }

    $korisnik = api_korisnik();
    $vijest = vijest_po_id($id);
    if ($vijest === null || $korisnik === null || !in_array($korisnik['uloga'], ['administrator', 'urednik'], true)
        || !api_smije_urediti_vijest($korisnik, $vijest)) {
        api_greska(404, 'Vijest ne postoji.');
    }

    api_odgovor(api_vijest($vijest, true, true));
}

// POST /v1/vijesti — nova vijest (administrator i urednik). Autor je prijavljeni korisnik.
function api_vijest_dodaj()
{
    $korisnik = api_zahtijevaj_ulogu(['administrator', 'urednik']);
    $tijelo = api_tijelo('vijest-nova');

    $status = $tijelo['status'] ?? 'nacrt';
    $vijest = [
        'kategorija_id' => $tijelo['kategorija_id'],
        'grad_opcina_id' => $tijelo['grad_opcina_id'] ?? null,
        'slika_id' => null,
        'naslov' => trim($tijelo['naslov']),
        'sazetak' => trim($tijelo['sazetak']),
        'sadrzaj' => api_sadrzaj_vijesti($tijelo['sadrzaj']),
        'status' => $status,
        'objavljeno_u' => api_datum_objave($tijelo['objavljeno_u'] ?? null, $status),
    ];
    api_provjeri_vijest($vijest);

    vijest_dodaj($vijest, $korisnik['id']);
    $id = mysqli_insert_id(db());

    header('Location: ' . api_adresa('api.php/v1/vijesti/' . $id));
    api_odgovor_vijest($id, 201);
}

// PATCH /v1/vijesti/{id} — mijenjaju se samo poslana polja
function api_vijest_izmijeni($id)
{
    $postojeca = api_vijest_za_izmjenu($id);
    $tijelo = api_tijelo('vijest-izmjena');

    $vijest = [
        'kategorija_id' => $tijelo['kategorija_id'] ?? $postojeca['kategorija_id'],
        'grad_opcina_id' => array_key_exists('grad_opcina_id', $tijelo) ? $tijelo['grad_opcina_id'] : $postojeca['grad_opcina_id'],
        'slika_id' => $postojeca['slika_id'],
        'naslov' => isset($tijelo['naslov']) ? trim($tijelo['naslov']) : $postojeca['naslov'],
        'sazetak' => isset($tijelo['sazetak']) ? trim($tijelo['sazetak']) : $postojeca['sazetak'],
        'sadrzaj' => isset($tijelo['sadrzaj']) ? api_sadrzaj_vijesti($tijelo['sadrzaj']) : $postojeca['sadrzaj'],
        'status' => $tijelo['status'] ?? $postojeca['status'],
    ];

    // Datum objave: poslani datum, inače postojeći, a ako ga nema (bio je nacrt) trenutno vrijeme
    if (array_key_exists('objavljeno_u', $tijelo)) {
        $vijest['objavljeno_u'] = api_datum_objave($tijelo['objavljeno_u'], $vijest['status']);
    } else {
        $vijest['objavljeno_u'] = $vijest['status'] === 'nacrt' ? null : ($postojeca['objavljeno_u'] ?? date('Y-m-d H:i:s'));
    }

    api_provjeri_vijest($vijest);

    vijest_uredi($id, $vijest);

    api_odgovor_vijest($id, 200);
}

// DELETE /v1/vijesti/{id} — briše vijest i njezinu sliku
function api_vijest_obrisi($id)
{
    $vijest = api_vijest_za_izmjenu($id);

    obrisi_vijest_sa_slikom($vijest);

    api_bez_sadrzaja();
}
