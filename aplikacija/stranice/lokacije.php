<?php

const LOKACIJE_PO_STRANICI = 6;
const RECENZIJA_PO_STRANICI = 10;

// Najveća duljina teksta recenzije u znakovima
const NAJVECI_TEKST_RECENZIJE = 1000;

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

// Provjera recenzije iz obrasca. Vraća polje grešaka po nazivu polja.
function provjeri_recenziju($ocjena, $tekst)
{
    $greske = [];

    if ($ocjena < 1 || $ocjena > 5) {
        $greske['ocjena'] = 'Odaberi ocjenu od 1 do 5.';
    }

    if (mb_strlen($tekst) > NAJVECI_TEKST_RECENZIJE) {
        $greske['tekst'] = 'Komentar može imati najviše ' . NAJVECI_TEKST_RECENZIJE . ' znakova.';
    }

    return $greske;
}

// Detalj lokacije: ?stranica=lokacija&id=5. Skrivena ili nepostojeća lokacija daje 404.
// Recenzije se straniče s ?str=2, po RECENZIJA_PO_STRANICI na stranici.
// Korisnik obrascem na ovoj stranici dodaje ili mijenja svoju recenziju.
function stranica_lokacija()
{
    $lokacija = lokacija_javna_po_id(get_broj('id'));

    if ($lokacija === null) {
        stranica_nije_pronadena();
        return;
    }

    $smije_recenzirati = ima_ulogu(['korisnik']);
    $vlastita = $smije_recenzirati ? recenzija_korisnika($lokacija['id'], trenutni_korisnik()['id']) : null;
    $obrazac = ['ocjena' => (int) ($vlastita['ocjena'] ?? 0), 'tekst' => $vlastita['tekst'] ?? ''];
    $greske = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!$smije_recenzirati) {
            stranica_zabranjeno('Recenzije mogu pisati samo prijavljeni korisnici.');
            return;
        }

        $obrazac = ['ocjena' => post_broj('ocjena'), 'tekst' => trim(post_tekst('tekst'))];
        $greske = provjeri_recenziju($obrazac['ocjena'], $obrazac['tekst']);

        if (count($greske) === 0) {
            recenzija_spremi($lokacija['id'], trenutni_korisnik()['id'], $obrazac['ocjena'], $obrazac['tekst'] !== '' ? $obrazac['tekst'] : null);
            postavi_poruku('uspjeh', $vlastita === null ? 'Hvala! Tvoja recenzija je objavljena.' : 'Tvoja recenzija je spremljena.');
            preusmjeri('index.php?stranica=lokacija&id=' . $lokacija['id'] . '#recenzije');
        }
    }

    $sazetak = recenzije_sazetak($lokacija['id']);
    $broj_stranica = max(1, (int) ceil($sazetak['broj'] / RECENZIJA_PO_STRANICI));
    $trenutna_stranica = min(max(1, get_broj('str')), $broj_stranica);

    prikazi('lokacija', [
        'lokacija' => $lokacija,
        'fotografije' => fotografije_lokacije($lokacija['id']),
        'radno_vrijeme' => radno_vrijeme_za_prikaz($lokacija['radno_vrijeme']),
        'sazetak' => $sazetak,
        'recenzije' => recenzije_javne($lokacija['id'], RECENZIJA_PO_STRANICI, ($trenutna_stranica - 1) * RECENZIJA_PO_STRANICI),
        'trenutna_stranica' => $trenutna_stranica,
        'broj_stranica' => $broj_stranica,
        'adresa' => ['stranica' => 'lokacija', 'id' => $lokacija['id']],
        'prijavljen' => trenutni_korisnik() !== null,
        'smije_recenzirati' => $smije_recenzirati,
        'vlastita' => $vlastita,
        'obrazac' => $obrazac,
        'greske' => $greske,
    ]);
}

// Brisanje vlastite recenzije: POST na ?stranica=recenzija-obrisi&lokacija=5
function stranica_recenzija_obrisi()
{
    $korisnik = zahtijevaj_ulogu(['korisnik']);
    $lokacija = lokacija_javna_po_id(get_broj('lokacija'));

    if ($lokacija === null) {
        stranica_nije_pronadena();
        return;
    }

    // Brisanje ide samo gumbom iz obrasca, a otvaranje adrese vraća na lokaciju
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        recenzija_obrisi($lokacija['id'], $korisnik['id']);
        postavi_poruku('uspjeh', 'Tvoja recenzija je obrisana.');
    }

    preusmjeri('index.php?stranica=lokacija&id=' . $lokacija['id'] . '#recenzije');
}
