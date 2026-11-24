<?php
// „Moj plan”: planovi putovanja prijavljenog korisnika. Svaki korisnik vidi i mijenja samo svoje planove.

const PLAN_NAJVISE_DANA = 14;
const PLAN_NAJDULJI_NAZIV = 100;
const PLAN_REZULTATA_PO_STRANICI = 10;

// Datum iz polja type="date" (Y-m-d). Prazno polje daje null, a neispravan datum false.
function datum_iz_obrasca($tekst)
{
    $tekst = trim($tekst);

    if ($tekst === '') {
        return null;
    }

    $datum = date_create_from_format('!Y-m-d', $tekst);

    // Nepostojeći datum (npr. 31. 2.)
    if ($datum === false || date_get_last_errors() !== false) {
        return false;
    }

    return date_format($datum, 'Y-m-d');
}

// Broj dana u razdoblju, uključujući prvi i zadnji dan (round zbog dana s 23 ili 25 sati pri promjeni vremena)
function broj_dana($datum_od, $datum_do)
{
    return (int) round((strtotime($datum_do) - strtotime($datum_od)) / 86400) + 1;
}

// Provjera naziva i razdoblja plana. Vraća greške po poljima.
// Novi plan ne može početi u prošlosti; postojeći smije zadržati svoj prvi dan ($dopusteni_od) i kad je prošao.
function provjeri_plan($naziv, $datum_od, $datum_do, $dopusteni_od = null)
{
    $greske = [];

    if ($naziv === '') {
        $greske['naziv'] = 'Upiši naziv plana.';
    } elseif (mb_strlen($naziv) > PLAN_NAJDULJI_NAZIV) {
        $greske['naziv'] = 'Naziv može imati najviše ' . PLAN_NAJDULJI_NAZIV . ' znakova.';
    }

    if ($datum_od === null) {
        $greske['datum_od'] = 'Odaberi prvi dan.';
    } elseif ($datum_od === false) {
        $greske['datum_od'] = 'Datum nije ispravan.';
    } elseif ($datum_od < date('Y-m-d') && $datum_od !== $dopusteni_od) {
        $greske['datum_od'] = 'Plan ne može početi u prošlosti.';
    }

    if ($datum_do === null) {
        $greske['datum_do'] = 'Odaberi zadnji dan.';
    } elseif ($datum_do === false) {
        $greske['datum_do'] = 'Datum nije ispravan.';
    } elseif (is_string($datum_od) && $datum_do < $datum_od) {
        $greske['datum_do'] = 'Zadnji dan ne može biti prije prvog.';
    } elseif (is_string($datum_od) && broj_dana($datum_od, $datum_do) > PLAN_NAJVISE_DANA) {
        $greske['datum_do'] = 'Plan može trajati najviše ' . PLAN_NAJVISE_DANA . ' dana.';
    }

    return $greske;
}

// Popis vlastitih planova i obrazac za novi plan: ?stranica=moj-plan
function stranica_moj_plan()
{
    $korisnik = zahtijevaj_prijavu();
    $obrazac = ['naziv' => '', 'datum_od' => '', 'datum_do' => ''];
    $greske = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $obrazac = [
            'naziv' => trim(post_tekst('naziv')),
            'datum_od' => post_tekst('datum_od'),
            'datum_do' => post_tekst('datum_do'),
        ];
        $datum_od = datum_iz_obrasca($obrazac['datum_od']);
        $datum_do = datum_iz_obrasca($obrazac['datum_do']);
        $greske = provjeri_plan($obrazac['naziv'], $datum_od, $datum_do);

        if (count($greske) === 0) {
            $id = plan_dodaj($korisnik['id'], $obrazac['naziv'], $datum_od, $datum_do);
            postavi_poruku('uspjeh', 'Plan „' . $obrazac['naziv'] . '” je stvoren. Sad mu dodaj lokacije i događanja.');
            preusmjeri('index.php?stranica=plan&id=' . $id);
        }
    }

    prikazi('moj-plan', [
        'planovi' => planovi_korisnika($korisnik['id']),
        'obrazac' => $obrazac,
        'greske' => $greske,
    ]);
}

// Plan prijavljenog korisnika iz adrese (?id=). Neprijavljenog šalje na prijavu, a za tuđi ili nepostojeći plan
// prikazuje 404 i vraća null, pa se ne otkriva ni da plan postoji.
function plan_iz_adrese()
{
    $korisnik = zahtijevaj_prijavu();
    $plan = plan_korisnika(get_broj('id'), $korisnik['id']);

    if ($plan === null) {
        stranica_nije_pronadena();
    }

    return $plan;
}

// Plan po danima: ?stranica=plan&id=1. Svaki dan razdoblja ima svoj popis stavki (i prazan dan se prikazuje).
function stranica_plan()
{
    $plan = plan_iz_adrese();

    if ($plan === null) {
        return;
    }

    $dani = [];
    for ($dan = $plan['datum_od']; $dan <= $plan['datum_do']; $dan = date('Y-m-d', strtotime($dan . ' +1 day'))) {
        $dani[$dan] = [];
    }

    foreach (stavke_plana($plan['id']) as $stavka) {
        $dani[$stavka['dan']][] = $stavka;
    }

    prikazi('plan', [
        'plan' => $plan,
        'dani' => $dani,
    ]);
}

// Uređivanje naziva i razdoblja plana: ?stranica=plan-uredi&id=1.
// Ako bi novo razdoblje izbacilo dane sa stavkama, traži se potvrda, a stavke s tih dana se brišu.
function stranica_plan_uredi()
{
    $plan = plan_iz_adrese();

    if ($plan === null) {
        return;
    }

    $obrazac = ['naziv' => $plan['naziv'], 'datum_od' => $plan['datum_od'], 'datum_do' => $plan['datum_do']];
    $greske = [];
    $stavki_izvan = 0;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $obrazac = [
            'naziv' => trim(post_tekst('naziv')),
            'datum_od' => post_tekst('datum_od'),
            'datum_do' => post_tekst('datum_do'),
        ];
        $datum_od = datum_iz_obrasca($obrazac['datum_od']);
        $datum_do = datum_iz_obrasca($obrazac['datum_do']);
        $greske = provjeri_plan($obrazac['naziv'], $datum_od, $datum_do, $plan['datum_od']);

        if (count($greske) === 0) {
            $stavki_izvan = plan_broj_stavki_izvan($plan['id'], $datum_od, $datum_do);

            if ($stavki_izvan > 0 && post_tekst('potvrda') !== '1') {
                $greske['potvrda'] = 'Potvrdi da se te stavke smiju obrisati ili promijeni datume.';
            }
        }

        if (count($greske) === 0) {
            $obrisano = plan_uredi($plan['id'], $obrazac['naziv'], $datum_od, $datum_do);
            postavi_poruku('uspjeh', 'Plan je spremljen.' . ($obrisano > 0 ? ' Obrisano stavki izvan novog razdoblja: ' . $obrisano . '.' : ''));
            preusmjeri('index.php?stranica=plan&id=' . $plan['id']);
        }
    }

    prikazi('plan-uredi', [
        'plan' => $plan,
        'obrazac' => $obrazac,
        'greske' => $greske,
        'stavki_izvan' => $stavki_izvan,
    ]);
}

// Brisanje plana uz potvrdu: ?stranica=plan-obrisi&id=1 (GET prikazuje potvrdu, POST briše)
function stranica_plan_obrisi()
{
    $plan = plan_iz_adrese();

    if ($plan === null) {
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        plan_obrisi($plan['id']);
        postavi_poruku('uspjeh', 'Plan „' . $plan['naziv'] . '” je obrisan.');
        preusmjeri('index.php?stranica=moj-plan');
    }

    prikazi('plan-obrisi', [
        'plan' => $plan,
        'broj_stavki' => count(stavke_plana($plan['id'])),
    ]);
}

// Vrijeme iz polja type="time" (HH:MM) u obliku za bazu. Prazno polje daje null, a neispravno vrijeme false.
function vrijeme_iz_obrasca($tekst)
{
    $tekst = trim($tekst);

    if ($tekst === '') {
        return null;
    }

    return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $tekst) ? $tekst . ':00' : false;
}

// Traje li događanje na zadani dan (Y-m-d). Događanje bez završetka traje samo dan početka.
function dogadanje_traje_na_dan($dogadanje, $dan)
{
    $prvi = substr($dogadanje['pocetak'], 0, 10);
    $zadnji = substr($dogadanje['zavrsetak'] ?? $dogadanje['pocetak'], 0, 10);

    return $prvi <= $dan && $dan <= $zadnji;
}

// Dodavanje lokacije ili događanja na dan plana: ?stranica=plan-dodaj&id=1&dan=2026-11-28
// GET: pretraga (naziv), županija -> grad/općina i izbor Lokacije/Događanja. Događanja su samo ona koja traju taj dan.
// Pri prvom otvaranju filtar je postavljen na grad prve stavke tog dana.
// POST: dodaje odabranu stavku (uz neobavezno vrijeme) i vraća korisnika na plan.
function stranica_plan_dodaj()
{
    $plan = plan_iz_adrese();

    if ($plan === null) {
        return;
    }

    $dan = get_datum('dan');
    if ($dan === null || $dan < $plan['datum_od'] || $dan > $plan['datum_do']) {
        stranica_nije_pronadena();
        return;
    }

    $povratak = 'index.php?stranica=plan&id=' . $plan['id'] . '#dan-' . $dan;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $vrsta = post_tekst('vrsta_stavke');
        $vrijeme = vrijeme_iz_obrasca(post_tekst('vrijeme'));
        $lokacija = $vrsta === 'lokacija' ? lokacija_javna_po_id(post_broj('stavka')) : null;
        $dogadanje = $vrsta === 'dogadanje' ? dogadanje_javno_po_id(post_broj('stavka')) : null;

        if ($lokacija === null && $dogadanje === null) {
            postavi_poruku('greska', 'Odabrana lokacija ili događanje ne postoji.');
        } elseif ($dogadanje !== null && !dogadanje_traje_na_dan($dogadanje, $dan)) {
            postavi_poruku('greska', 'Događanje „' . $dogadanje['naziv'] . '” ne traje taj dan.');
        } elseif ($vrijeme === false) {
            postavi_poruku('greska', 'Vrijeme nije ispravno. Upiši ga u obliku 10:30 ili ostavi prazno.');
        } else {
            $naziv = $lokacija['naziv'] ?? $dogadanje['naziv'];
            $dodano = stavka_dodaj($plan['id'], $dan, $vrijeme, $lokacija['id'] ?? null, $dogadanje['id'] ?? null);

            if ($dodano) {
                postavi_poruku('uspjeh', '„' . $naziv . '” je dodano u plan.');
            } else {
                postavi_poruku('greska', '„' . $naziv . '” je već u planu taj dan.');
            }
            preusmjeri($povratak);
        }

        // Greška: natrag na istu pretragu
        preusmjeri('index.php?' . http_build_query($_GET));
    }

    $zupanije = zupanije_sve();
    $vrsta_stavke = get_tekst('vrsta_stavke') === 'dogadanja' ? 'dogadanja' : 'lokacije';
    $prvo_otvaranje = !isset($_GET['zupanija']) && !isset($_GET['grad_opcina']) && !isset($_GET['trazi']);
    $u_planu = stavke_dana($plan['id'], $dan);

    if ($prvo_otvaranje) {
        $stavke = array_values(array_filter(stavke_plana($plan['id']), fn($stavka) => $stavka['dan'] === $dan));
        $grad_opcina = count($stavke) > 0 ? grad_opcina_po_id($stavke[0]['grad_opcina_id']) : null;
        $zupanija_id = $grad_opcina !== null ? (int) $grad_opcina['zupanija_id'] : 0;
        $grad_opcina_id = $grad_opcina !== null ? (int) $grad_opcina['id'] : 0;
    } else {
        [$zupanija_id, $grad_opcina_id] = odabrano_podrucje($zupanije);
    }

    $filtar = [
        'zupanija' => $zupanija_id,
        'grad_opcina' => $grad_opcina_id,
        'trazi' => get_tekst('trazi'),
        'od' => $dan,
        'do' => $dan,
    ];

    $ukupno = $vrsta_stavke === 'lokacije' ? lokacije_javne_broj($filtar) : dogadanja_javna_broj($filtar);
    $broj_stranica = max(1, (int) ceil($ukupno / PLAN_REZULTATA_PO_STRANICI));
    $trenutna_stranica = min(max(1, get_broj('str')), $broj_stranica);
    $preskoci = ($trenutna_stranica - 1) * PLAN_REZULTATA_PO_STRANICI;

    $rezultati = $vrsta_stavke === 'lokacije'
        ? lokacije_javne($filtar, PLAN_REZULTATA_PO_STRANICI, $preskoci)
        : dogadanja_javna($filtar, PLAN_REZULTATA_PO_STRANICI, $preskoci);

    // Parametri adrese bez stranice, da straničenje i obrasci za dodavanje zadrže filtar
    $adresa = array_filter([
        'stranica' => 'plan-dodaj',
        'id' => $plan['id'],
        'dan' => $dan,
        'vrsta_stavke' => $vrsta_stavke,
        'trazi' => $filtar['trazi'],
        'zupanija' => $zupanija_id,
        'grad_opcina' => $grad_opcina_id,
    ]);

    prikazi('plan-dodaj', [
        'plan' => $plan,
        'dan' => $dan,
        'vrsta_stavke' => $vrsta_stavke,
        'rezultati' => $rezultati,
        'ukupno' => $ukupno,
        'u_planu' => $u_planu,
        'trenutna_stranica' => $trenutna_stranica,
        'broj_stranica' => $broj_stranica,
        'adresa' => $adresa,
        'zupanije' => $zupanije,
        'gradovi_opcine' => gradovi_opcine_svi(),
        'odabrana_zupanija' => $zupanija_id,
        'odabrani_grad_opcina' => $grad_opcina_id,
        'trazi' => $filtar['trazi'],
    ]);
}
