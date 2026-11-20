<?php
// Administracija lokacija. Lokacije uređuju administrator i urednik, a vrste lokacija samo administrator.

const ADMIN_LOKACIJA_PO_STRANICI = 20;

// Najveća duljina opisa lokacije u znakovima
const NAJVECI_OPIS_LOKACIJE = 5000;

// Najveći broj fotografija jedne lokacije
const NAJVISE_FOTOGRAFIJA = 5;

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

// Datoteke poslane poljem s atributom multiple (name="fotografije[]"), kao popis zasebnih datoteka.
// PHP ih šalje kao polje po svojstvima (name => […], tmp_name => […]), pa se preslažu po datoteci.
// Prazna polja (ništa nije odabrano) se preskaču.
function datoteke_iz_obrasca($kljuc)
{
    $poslano = $_FILES[$kljuc] ?? null;

    if (!is_array($poslano) || !is_array($poslano['error'] ?? null)) {
        return [];
    }

    $datoteke = [];

    foreach (array_keys($poslano['error']) as $i) {
        if ($poslano['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $datoteke[] = [
            'name' => $poslano['name'][$i],
            'tmp_name' => $poslano['tmp_name'][$i],
            'size' => $poslano['size'][$i],
            'error' => $poslano['error'][$i],
        ];
    }

    return $datoteke;
}

// Fotografije iz obrasca: koje se uklanjaju i kojim redom ostaju postojeće.
// Vraća [id-evi slika za uklanjanje, id-evi preostalih slika po novom redoslijedu].
function fotografije_iz_posta($postojece)
{
    $ids = array_map('intval', array_column($postojece, 'id'));
    $ukloni = is_array($_POST['ukloni_fotografije'] ?? null) ? array_map('intval', $_POST['ukloni_fotografije']) : [];
    $ukloni = array_values(array_intersect($ids, $ukloni));
    $redoslijed = is_array($_POST['redoslijed'] ?? null) ? $_POST['redoslijed'] : [];

    // Preostale slike slažu se po upisanom broju, a uz isti broj ostaje dosadašnji poredak
    $preostale = [];
    foreach ($ids as $i => $id) {
        if (!in_array($id, $ukloni, true)) {
            $broj = is_string($redoslijed[$id] ?? null) && ctype_digit($redoslijed[$id]) ? (int) $redoslijed[$id] : 99;
            $preostale[] = [$broj, $i, $id];
        }
    }
    sort($preostale);

    return [$ukloni, array_column($preostale, 2)];
}

// Sprema lokaciju i njezine fotografije u jednoj transakciji. Ako bilo što ne uspije, u bazi se ništa ne mijenja.
// $nove_slike su podaci iz prijenos_slike(), $ukloni id-evi slika za brisanje, a $poredak id-evi preostalih slika.
// Vraća id lokacije.
function spremi_lokaciju($postojeca, $lokacija, $nove_slike, $ukloni, $poredak, $autor_id)
{
    $slike_za_brisanje = array_map('slika_po_id', $ukloni);

    mysqli_begin_transaction(db());

    try {
        if ($postojeca === null) {
            $id = lokacija_dodaj($lokacija, $autor_id);
        } else {
            $id = (int) $postojeca['id'];
            lokacija_uredi($id, $lokacija);
        }

        // Brisanjem slike briše se i njezina veza s lokacijom (ON DELETE CASCADE)
        foreach ($ukloni as $slika_id) {
            slika_obrisi($slika_id);
        }

        $redoslijed = 1;
        foreach ($poredak as $slika_id) {
            fotografija_redoslijed($id, $slika_id, $redoslijed++);
        }

        // Nove fotografije dolaze iza postojećih
        foreach ($nove_slike as $slika) {
            fotografija_dodaj($id, slika_dodaj($slika, $autor_id), $redoslijed++);
        }

        mysqli_commit(db());
    } catch (Throwable $greska) {
        // Baza se vraća na stanje prije spremanja, a nove slike brišu se iz mape prijenosi/
        mysqli_rollback(db());
        foreach ($nove_slike as $slika) {
            obrisi_datoteku_slike($slika['datoteka']);
        }
        throw $greska;
    }

    // Datoteke uklonjenih slika brišu se tek kad je sve spremljeno u bazu
    foreach ($slike_za_brisanje as $slika) {
        if ($slika !== null) {
            obrisi_datoteku_slike($slika['datoteka']);
        }
    }

    return $id;
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
    $fotografije = $postojeca !== null ? fotografije_lokacije($postojeca['id']) : [];
    $ukloni = [];
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

        [$ukloni, $poredak] = fotografije_iz_posta($fotografije);
        $datoteke = datoteke_iz_obrasca('fotografije');

        if (count($poredak) + count($datoteke) > NAJVISE_FOTOGRAFIJA) {
            $greske['fotografije'] = 'Lokacija može imati najviše ' . NAJVISE_FOTOGRAFIJA . ' fotografija. '
                . 'Sada bi ih imala ' . (count($poredak) + count($datoteke)) . '.';
        }

        // Slike se prenose tek kad su ostala polja ispravna, inače bi u mapi prijenosi/ ostale slike bez lokacije
        $nove_slike = [];

        if (count($greske) === 0 && count($greske_radnog_vremena) === 0) {
            foreach ($datoteke as $datoteka) {
                [$slika, $greska_slike] = prijenos_slike($datoteka);

                if ($greska_slike !== null) {
                    $greske['fotografije'] = $datoteka['name'] . ': ' . $greska_slike;
                    break;
                }

                $nove_slike[] = $slika;
            }

            // Ako jedna slika ne prođe, već prenesene se brišu
            if (isset($greske['fotografije'])) {
                foreach ($nove_slike as $slika) {
                    obrisi_datoteku_slike($slika['datoteka']);
                }
            }
        }

        if (count($greske) === 0 && count($greske_radnog_vremena) === 0) {
            // Bez upisanih koordinata lokacija dobiva koordinate sjedišta grada/općine
            if ($lokacija['lat'] === null) {
                $grad_opcina = grad_opcina_po_id($lokacija['grad_opcina_id']);
                $lokacija['lat'] = (float) $grad_opcina['lat'];
                $lokacija['lng'] = (float) $grad_opcina['lng'];
            }

            spremi_lokaciju($postojeca, $lokacija, $nove_slike, $ukloni, $poredak, $korisnik['id']);

            $stanje = $lokacija['objavljeno'] === 1 ? 'objavljena' : 'spremljena kao skrivena';
            postavi_poruku('uspjeh', 'Lokacija „' . $lokacija['naziv'] . '” je ' . $stanje . '.');
            preusmjeri('index.php?stranica=admin-lokacije');
        }
    }

    prikazi('admin-lokacija', [
        'postojeca' => $postojeca,
        'lokacija' => $lokacija,
        'radno_vrijeme' => $radno_vrijeme,
        'fotografije' => $fotografije,
        'ukloni' => $ukloni,
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

// Brisanje lokacije uz potvrdu, zajedno s njezinim fotografijama
function stranica_admin_lokacija_obrisi()
{
    zahtijevaj_ulogu(['administrator', 'urednik']);

    $lokacija = lokacija_po_id(get_broj('id'));

    if ($lokacija === null) {
        stranica_nije_pronadena();
        return;
    }

    $fotografije = fotografije_lokacije($lokacija['id']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Lokacija i podaci o njezinim slikama brišu se zajedno ili se ne briše ništa
        mysqli_begin_transaction(db());

        try {
            foreach ($fotografije as $fotografija) {
                slika_obrisi($fotografija['id']);
            }

            lokacija_obrisi($lokacija['id']);

            mysqli_commit(db());
        } catch (Throwable $greska) {
            mysqli_rollback(db());
            throw $greska;
        }

        // Datoteke slika brišu se tek kad je brisanje u bazi uspjelo
        foreach ($fotografije as $fotografija) {
            obrisi_datoteku_slike($fotografija['datoteka']);
        }

        postavi_poruku('uspjeh', 'Lokacija „' . $lokacija['naziv'] . '” je obrisana.');
        preusmjeri('index.php?stranica=admin-lokacije');
    }

    prikazi('admin-lokacija-obrisi', [
        'lokacija' => $lokacija,
        'broj_fotografija' => count($fotografije),
    ]);
}
