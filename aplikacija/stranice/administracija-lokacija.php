<?php
// Administracija lokacija. Lokacije uređuju administrator i urednik, a vrste lokacija samo administrator.

const ADMIN_LOKACIJA_PO_STRANICI = 20;

// Najveća duljina opisa lokacije u znakovima
const NAJVECI_OPIS_LOKACIJE = 5000;

// Koordinate lokacije moraju biti unutar pravokutnika oko Hrvatske
const GRANICE_HRVATSKE = ['lat_min' => 42.3, 'lat_max' => 46.6, 'lng_min' => 13.4, 'lng_max' => 19.5];

function provjeri_vrstu_lokacije($naziv, $osim_id = 0)
{
    $greske = [];

    if ($naziv === '') {
        $greske['naziv'] = 'Upiši naziv vrste.';
    } elseif (mb_strlen($naziv) > 60) {
        $greske['naziv'] = 'Naziv može imati najviše 60 znakova.';
    } elseif (vrsta_lokacije_naziv_zauzet($naziv, $osim_id)) {
        $greske['naziv'] = 'Vrsta s tim nazivom već postoji.';
    }

    return $greske;
}

// Popis vrsta lokacija
function stranica_admin_vrste_lokacija()
{
    zahtijevaj_ulogu(['administrator']);

    prikazi('admin-vrste-lokacija', [
        'vrste' => vrste_lokacija_s_brojem_lokacija(),
    ]);
}

// Dodavanje vrste lokacije i uređivanje postojeće
function stranica_admin_vrsta_lokacije()
{
    zahtijevaj_ulogu(['administrator']);

    $vrsta = null;
    $id = get_broj('id');

    if ($id > 0) {
        $vrsta = vrsta_lokacije_po_id($id);

        if ($vrsta === null) {
            stranica_nije_pronadena();
            return;
        }
    }

    $naziv = $vrsta['naziv'] ?? '';
    $greske = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $naziv = trim(post_tekst('naziv'));
        $greske = provjeri_vrstu_lokacije($naziv, $id);

        if (count($greske) === 0) {
            if ($vrsta === null) {
                vrsta_lokacije_dodaj($naziv);
                postavi_poruku('uspjeh', 'Vrsta „' . $naziv . '” je dodana.');
            } else {
                vrsta_lokacije_uredi($id, $naziv);
                postavi_poruku('uspjeh', 'Vrsta „' . $naziv . '” je spremljena.');
            }
            preusmjeri('index.php?stranica=admin-vrste-lokacija');
        }
    }

    prikazi('admin-vrsta-lokacije', [
        'vrsta' => $vrsta,
        'naziv' => $naziv,
        'greske' => $greske,
    ]);
}

// Brisanje vrste lokacije. Vrsta koju koristi neka lokacija ne može se obrisati.
function stranica_admin_vrsta_lokacije_obrisi()
{
    zahtijevaj_ulogu(['administrator']);

    $vrsta = vrsta_lokacije_po_id(get_broj('id'));

    if ($vrsta === null) {
        stranica_nije_pronadena();
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (vrsta_lokacije_obrisi($vrsta['id'])) {
            postavi_poruku('uspjeh', 'Vrsta „' . $vrsta['naziv'] . '” je obrisana.');
        } else {
            postavi_poruku('greska', 'Vrsta „' . $vrsta['naziv'] . '” se ne može obrisati jer je koriste lokacije.');
        }
        preusmjeri('index.php?stranica=admin-vrste-lokacija');
    }

    prikazi('admin-vrsta-lokacije-obrisi', [
        'vrsta' => $vrsta,
        'broj_lokacija' => vrsta_lokacije_broj_lokacija($vrsta['id']),
    ]);
}

// Popis svih lokacija (i skrivenih) s pretragom i straničenjem
function stranica_admin_lokacije()
{
    zahtijevaj_ulogu(['administrator', 'urednik']);

    $trazi = get_tekst('trazi');

    $ukupno = lokacije_admin_broj($trazi);
    $broj_stranica = max(1, (int) ceil($ukupno / ADMIN_LOKACIJA_PO_STRANICI));
    $trenutna_stranica = min(max(1, get_broj('str')), $broj_stranica);

    prikazi('admin-lokacije', [
        'lokacije' => lokacije_admin($trazi, ADMIN_LOKACIJA_PO_STRANICI, ($trenutna_stranica - 1) * ADMIN_LOKACIJA_PO_STRANICI),
        'trazi' => $trazi,
        'trenutna_stranica' => $trenutna_stranica,
        'broj_stranica' => $broj_stranica,
        // Poveznice straničenja zadržavaju pretragu
        'adresa' => array_filter(['stranica' => 'admin-lokacije', 'trazi' => $trazi]),
    ]);
}

// Koordinata iz obrasca: prihvaća decimalnu točku ili zarez. Prazno daje null, a neispravan broj false.
function koordinata_iz_obrasca($tekst)
{
    $tekst = str_replace(',', '.', trim($tekst));

    if ($tekst === '') {
        return null;
    }

    return is_numeric($tekst) ? round((float) $tekst, 6) : false;
}

function unutar_hrvatske($lat, $lng)
{
    return $lat >= GRANICE_HRVATSKE['lat_min'] && $lat <= GRANICE_HRVATSKE['lat_max']
        && $lng >= GRANICE_HRVATSKE['lng_min'] && $lng <= GRANICE_HRVATSKE['lng_max'];
}

// Radno vrijeme iz baze (JSON) za polja obrasca: ['ima' => bool, 'dani' => [pon => [od, do, zatvoreno], …], 'napomena' => tekst]
function radno_vrijeme_za_obrazac($json)
{
    $podaci = $json !== null ? json_decode($json, true) : null;
    $obrazac = ['ima' => is_array($podaci), 'dani' => [], 'napomena' => ''];

    foreach (array_keys(DANI_U_TJEDNU) as $dan) {
        $vrijeme = $podaci[$dan] ?? null;
        $otvoreno = is_array($vrijeme) && count($vrijeme) === 2;
        $obrazac['dani'][$dan] = [$otvoreno ? $vrijeme[0] : '', $otvoreno ? $vrijeme[1] : '', is_array($podaci) && !$otvoreno];
    }

    if (is_array($podaci) && is_string($podaci['napomena'] ?? null)) {
        $obrazac['napomena'] = $podaci['napomena'];
    }

    return $obrazac;
}

// Radno vrijeme iz poslanog obrasca, u istom obliku kao radno_vrijeme_za_obrazac()
function radno_vrijeme_iz_posta()
{
    $poslano = is_array($_POST['radno_vrijeme'] ?? null) ? $_POST['radno_vrijeme'] : [];
    $obrazac = ['ima' => post_tekst('ima_radno_vrijeme') === '1', 'dani' => [], 'napomena' => ''];

    foreach (array_keys(DANI_U_TJEDNU) as $dan) {
        $polja = is_array($poslano[$dan] ?? null) ? $poslano[$dan] : [];
        $tekst = function ($kljuc) use ($polja) {
            return is_string($polja[$kljuc] ?? null) ? trim($polja[$kljuc]) : '';
        };
        $obrazac['dani'][$dan] = [$tekst('od'), $tekst('do'), $tekst('zatvoreno') === '1'];
    }

    $obrazac['napomena'] = is_string($poslano['napomena'] ?? null) ? trim($poslano['napomena']) : '';

    return $obrazac;
}

// Vrijeme u obliku SS:MM, npr. 09:30
function ispravno_vrijeme($tekst)
{
    return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $tekst) === 1;
}

// Provjera radnog vremena iz obrasca. Vraća [JSON za bazu ili null, polje grešaka po danu].
// Bez kvačice „Ima radno vrijeme” lokacija je uvijek otvorena (null).
function radno_vrijeme_u_json($obrazac)
{
    if (!$obrazac['ima']) {
        return [null, []];
    }

    $podaci = [];
    $greske = [];

    foreach ($obrazac['dani'] as $dan => [$od, $do, $zatvoreno]) {
        if ($zatvoreno) {
            $podaci[$dan] = null;
        } elseif (!ispravno_vrijeme($od) || !ispravno_vrijeme($do)) {
            $greske[$dan] = DANI_U_TJEDNU[$dan] . ': upiši vrijeme otvaranja i zatvaranja ili označi da je zatvoreno.';
        } elseif ($od >= $do) {
            $greske[$dan] = DANI_U_TJEDNU[$dan] . ': vrijeme zatvaranja mora biti nakon vremena otvaranja.';
        } else {
            $podaci[$dan] = [$od, $do];
        }
    }

    if (mb_strlen($obrazac['napomena']) > 200) {
        $greske['napomena'] = 'Napomena može imati najviše 200 znakova.';
    } elseif ($obrazac['napomena'] !== '') {
        $podaci['napomena'] = $obrazac['napomena'];
    }

    return [json_encode($podaci, JSON_UNESCAPED_UNICODE), $greske];
}

// Provjera podataka lokacije. Vraća polje grešaka po nazivu polja.
function provjeri_lokaciju($lokacija, $zupanija_id, $osim_id = 0)
{
    $greske = [];

    if ($lokacija['naziv'] === '') {
        $greske['naziv'] = 'Upiši naziv lokacije.';
    } elseif (mb_strlen($lokacija['naziv']) > 150) {
        $greske['naziv'] = 'Naziv može imati najviše 150 znakova.';
    }

    if (vrsta_lokacije_po_id($lokacija['vrsta_id']) === null) {
        $greske['vrsta'] = 'Odaberi vrstu lokacije.';
    }

    $grad_opcina = $lokacija['grad_opcina_id'] > 0 ? grad_opcina_po_id($lokacija['grad_opcina_id']) : null;

    if ($grad_opcina === null) {
        $greske['grad_opcina'] = 'Odaberi grad ili općinu.';
    } elseif ($zupanija_id > 0 && (int) $grad_opcina['zupanija_id'] !== $zupanija_id) {
        $greske['grad_opcina'] = 'Odabrani grad ili općina nije u odabranoj županiji.';
    } elseif (!isset($greske['naziv']) && lokacija_naziv_zauzet($lokacija['naziv'], $grad_opcina['id'], $osim_id)) {
        $greske['naziv'] = 'U ovom gradu ili općini već postoji lokacija s tim nazivom.';
    }

    if (trim($lokacija['opis']) === '') {
        $greske['opis'] = 'Upiši opis lokacije.';
    } elseif (mb_strlen($lokacija['opis']) > NAJVECI_OPIS_LOKACIJE) {
        $greske['opis'] = 'Opis može imati najviše ' . NAJVECI_OPIS_LOKACIJE . ' znakova.';
    }

    if ($lokacija['adresa'] !== null && mb_strlen($lokacija['adresa']) > 200) {
        $greske['adresa'] = 'Adresa može imati najviše 200 znakova.';
    }

    // Upisuju se obje koordinate ili nijedna
    if ($lokacija['lat'] === false || $lokacija['lng'] === false) {
        $greske['koordinate'] = 'Koordinate moraju biti brojevi, npr. 45.815 i 15.982.';
    } elseif (($lokacija['lat'] === null) !== ($lokacija['lng'] === null)) {
        $greske['koordinate'] = 'Upiši obje koordinate ili ostavi obje prazne.';
    } elseif ($lokacija['lat'] !== null && !unutar_hrvatske($lokacija['lat'], $lokacija['lng'])) {
        $greske['koordinate'] = 'Koordinate moraju biti unutar Hrvatske.';
    }

    return $greske;
}

// Dodavanje lokacije i uređivanje postojeće (administrator i urednik, sve lokacije)
function stranica_admin_lokacija()
{
    $korisnik = zahtijevaj_ulogu(['administrator', 'urednik']);

    $postojeca = null;
    $id = get_broj('id');

    if ($id > 0) {
        $postojeca = lokacija_po_id($id);

        if ($postojeca === null) {
            stranica_nije_pronadena();
            return;
        }
    }

    $lokacija = [
        'vrsta_id' => (int) ($postojeca['vrsta_id'] ?? 0),
        'grad_opcina_id' => (int) ($postojeca['grad_opcina_id'] ?? 0),
        'naziv' => $postojeca['naziv'] ?? '',
        'opis' => $postojeca['opis'] ?? '',
        'adresa' => $postojeca['adresa'] ?? null,
        'lat' => $postojeca['lat'] ?? null,
        'lng' => $postojeca['lng'] ?? null,
        'radno_vrijeme' => $postojeca['radno_vrijeme'] ?? null,
        'objavljeno' => (int) ($postojeca['objavljeno'] ?? 0),
        'istaknuto' => (int) ($postojeca['istaknuto'] ?? 0),
    ];
    $radno_vrijeme = radno_vrijeme_za_obrazac($lokacija['radno_vrijeme']);
    $zupanija_id = (int) ($postojeca['zupanija_id'] ?? 0);
    $greske = [];
    $greske_radnog_vremena = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $adresa = trim(post_tekst('adresa'));

        $lokacija = [
            'vrsta_id' => post_broj('vrsta'),
            'grad_opcina_id' => post_broj('grad_opcina'),
            'naziv' => trim(post_tekst('naziv')),
            'opis' => trim(post_tekst('opis')),
            'adresa' => $adresa !== '' ? $adresa : null,
            'lat' => koordinata_iz_obrasca(post_tekst('lat')),
            'lng' => koordinata_iz_obrasca(post_tekst('lng')),
            'objavljeno' => post_tekst('objavljeno') === '1' ? 1 : 0,
            'istaknuto' => post_tekst('istaknuto') === '1' ? 1 : 0,
        ];
        $radno_vrijeme = radno_vrijeme_iz_posta();
        [$lokacija['radno_vrijeme'], $greske_radnog_vremena] = radno_vrijeme_u_json($radno_vrijeme);
        $zupanija_id = post_broj('zupanija');
        $greske = provjeri_lokaciju($lokacija, $zupanija_id, $id);

        if (count($greske) === 0 && count($greske_radnog_vremena) === 0) {
            // Bez upisanih koordinata lokacija dobiva koordinate sjedišta grada/općine
            if ($lokacija['lat'] === null) {
                $grad_opcina = grad_opcina_po_id($lokacija['grad_opcina_id']);
                $lokacija['lat'] = (float) $grad_opcina['lat'];
                $lokacija['lng'] = (float) $grad_opcina['lng'];
            }

            if ($postojeca === null) {
                lokacija_dodaj($lokacija, $korisnik['id']);
            } else {
                lokacija_uredi($postojeca['id'], $lokacija);
            }

            $stanje = $lokacija['objavljeno'] === 1 ? 'objavljena' : 'spremljena kao skrivena';
            postavi_poruku('uspjeh', 'Lokacija „' . $lokacija['naziv'] . '” je ' . $stanje . '.');
            preusmjeri('index.php?stranica=admin-lokacije');
        }
    }

    prikazi('admin-lokacija', [
        'postojeca' => $postojeca,
        'lokacija' => $lokacija,
        'radno_vrijeme' => $radno_vrijeme,
        'greske' => $greske,
        'greske_radnog_vremena' => $greske_radnog_vremena,
        'vrste' => vrste_lokacija_sve(),
        'zupanije' => zupanije_sve(),
        'gradovi_opcine' => gradovi_opcine_svi(),
        'odabrana_zupanija' => $zupanija_id,
        'odabrani_grad_opcina' => $lokacija['grad_opcina_id'],
        'oznaka_sve_zupanije' => 'Odaberi županiju',
        'oznaka_svi_gradovi' => 'Odaberi grad ili općinu',
    ]);
}
