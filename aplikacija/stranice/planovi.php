<?php
// „Moj plan”: planovi putovanja prijavljenog korisnika. Svaki korisnik vidi i mijenja samo svoje planove.

const PLAN_NAJVISE_DANA = 14;
const PLAN_NAJDULJI_NAZIV = 100;

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
function provjeri_plan($naziv, $datum_od, $datum_do)
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
    } elseif ($datum_od < date('Y-m-d')) {
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
            plan_dodaj($korisnik['id'], $obrazac['naziv'], $datum_od, $datum_do);
            postavi_poruku('uspjeh', 'Plan „' . $obrazac['naziv'] . '” je stvoren.');
            preusmjeri('index.php?stranica=moj-plan');
        }
    }

    prikazi('moj-plan', [
        'planovi' => planovi_korisnika($korisnik['id']),
        'obrazac' => $obrazac,
        'greske' => $greske,
    ]);
}
