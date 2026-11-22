<?php
// Administracija događanja. Događanja uređuju administrator i urednik, a vrste događanja samo administrator.

const ADMIN_DOGADANJA_PO_STRANICI = 20;

// Najveća duljina opisa događanja u znakovima
const NAJVECI_OPIS_DOGADANJA = 5000;

// Najveća cijena ulaznice u eurima
const NAJVECA_CIJENA = 10000;

function provjeri_vrstu_dogadanja($naziv, $osim_id = 0)
{
    $greske = [];

    if ($naziv === '') {
        $greske['naziv'] = 'Upiši naziv vrste.';
    } elseif (mb_strlen($naziv) > 60) {
        $greske['naziv'] = 'Naziv može imati najviše 60 znakova.';
    } elseif (vrsta_dogadanja_naziv_zauzet($naziv, $osim_id)) {
        $greske['naziv'] = 'Vrsta s tim nazivom već postoji.';
    }

    return $greske;
}

// Popis vrsta događanja
function stranica_admin_vrste_dogadanja()
{
    zahtijevaj_ulogu(['administrator']);

    prikazi('admin-vrste-dogadanja', [
        'vrste' => vrste_dogadanja_s_brojem_dogadanja(),
    ]);
}

// Dodavanje vrste događanja i uređivanje postojeće
function stranica_admin_vrsta_dogadanja()
{
    zahtijevaj_ulogu(['administrator']);

    $vrsta = null;
    $id = get_broj('id');

    if ($id > 0) {
        $vrsta = vrsta_dogadanja_po_id($id);

        if ($vrsta === null) {
            stranica_nije_pronadena();
            return;
        }
    }

    $naziv = $vrsta['naziv'] ?? '';
    $greske = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $naziv = trim(post_tekst('naziv'));
        $greske = provjeri_vrstu_dogadanja($naziv, $id);

        if (count($greske) === 0) {
            if ($vrsta === null) {
                vrsta_dogadanja_dodaj($naziv);
                postavi_poruku('uspjeh', 'Vrsta „' . $naziv . '” je dodana.');
            } else {
                vrsta_dogadanja_uredi($id, $naziv);
                postavi_poruku('uspjeh', 'Vrsta „' . $naziv . '” je spremljena.');
            }
            preusmjeri('index.php?stranica=admin-vrste-dogadanja');
        }
    }

    prikazi('admin-vrsta-dogadanja', [
        'vrsta' => $vrsta,
        'naziv' => $naziv,
        'greske' => $greske,
    ]);
}

// Brisanje vrste događanja. Vrsta koju koristi neko događanje ne može se obrisati.
function stranica_admin_vrsta_dogadanja_obrisi()
{
    zahtijevaj_ulogu(['administrator']);

    $vrsta = vrsta_dogadanja_po_id(get_broj('id'));

    if ($vrsta === null) {
        stranica_nije_pronadena();
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (vrsta_dogadanja_obrisi($vrsta['id'])) {
            postavi_poruku('uspjeh', 'Vrsta „' . $vrsta['naziv'] . '” je obrisana.');
        } else {
            postavi_poruku('greska', 'Vrsta „' . $vrsta['naziv'] . '” se ne može obrisati jer je koriste događanja.');
        }
        preusmjeri('index.php?stranica=admin-vrste-dogadanja');
    }

    prikazi('admin-vrsta-dogadanja-obrisi', [
        'vrsta' => $vrsta,
        'broj_dogadanja' => vrsta_dogadanja_broj_dogadanja($vrsta['id']),
    ]);
}

// Popis svih događanja (i skrivenih i prošlih) s pretragom i straničenjem
function stranica_admin_dogadanja()
{
    zahtijevaj_ulogu(['administrator', 'urednik']);

    $trazi = get_tekst('trazi');

    $ukupno = dogadanja_admin_broj($trazi);
    $broj_stranica = max(1, (int) ceil($ukupno / ADMIN_DOGADANJA_PO_STRANICI));
    $trenutna_stranica = min(max(1, get_broj('str')), $broj_stranica);

    prikazi('admin-dogadanja', [
        'dogadanja' => dogadanja_admin($trazi, ADMIN_DOGADANJA_PO_STRANICI, ($trenutna_stranica - 1) * ADMIN_DOGADANJA_PO_STRANICI),
        'trazi' => $trazi,
        'trenutna_stranica' => $trenutna_stranica,
        'broj_stranica' => $broj_stranica,
        // Poveznice straničenja zadržavaju pretragu
        'adresa' => array_filter(['stranica' => 'admin-dogadanja', 'trazi' => $trazi]),
    ]);
}

// Datum i vrijeme iz polja type="datetime-local" (npr. 2026-12-18T19:30) u obliku za bazu.
// Prazno polje daje null, a neispravan datum false.
function datum_vrijeme_iz_obrasca($tekst)
{
    $tekst = trim($tekst);

    if ($tekst === '') {
        return null;
    }

    $datum = date_create_from_format('Y-m-d\TH:i', $tekst);

    // Nepostojeći datum (npr. 31. 2.)
    if ($datum === false || date_get_last_errors() !== false) {
        return false;
    }

    return date_format($datum, 'Y-m-d H:i:00');
}

// Cijena iz obrasca u eurima: prihvaća decimalni zarez i znak €. Prazno daje null (besplatno), a neispravan iznos false.
function cijena_iz_obrasca($tekst)
{
    $tekst = str_replace([',', '€', ' '], ['.', '', ''], trim($tekst));

    if ($tekst === '') {
        return null;
    }

    return is_numeric($tekst) ? round((float) $tekst, 2) : false;
}

// Provjera podataka događanja. Vraća polje grešaka po nazivu polja.
function provjeri_dogadanje($dogadanje, $zupanija_id)
{
    $greske = [];

    if ($dogadanje['naziv'] === '') {
        $greske['naziv'] = 'Upiši naziv događanja.';
    } elseif (mb_strlen($dogadanje['naziv']) > 150) {
        $greske['naziv'] = 'Naziv može imati najviše 150 znakova.';
    }

    if (vrsta_dogadanja_po_id($dogadanje['vrsta_id']) === null) {
        $greske['vrsta'] = 'Odaberi vrstu događanja.';
    }

    $grad_opcina = $dogadanje['grad_opcina_id'] > 0 ? grad_opcina_po_id($dogadanje['grad_opcina_id']) : null;

    if ($grad_opcina === null) {
        $greske['grad_opcina'] = 'Odaberi lokaciju ili grad/općinu.';
    } elseif ($zupanija_id > 0 && (int) $grad_opcina['zupanija_id'] !== $zupanija_id) {
        $greske['grad_opcina'] = 'Odabrani grad ili općina nije u odabranoj županiji.';
    }

    // Lokacija nije obavezna. Ako su odabrani i lokacija i grad/općina, lokacija mora biti u tom gradu/općini.
    if ($dogadanje['lokacija_id'] !== null) {
        $lokacija = lokacija_grad_opcina($dogadanje['lokacija_id']);

        if ($lokacija === null) {
            $greske['lokacija'] = 'Odabrana lokacija ne postoji.';
        } elseif ($grad_opcina !== null && (int) $lokacija['grad_opcina_id'] !== (int) $grad_opcina['id']) {
            $greske['lokacija'] = 'Odabrana lokacija nije u odabranom gradu ili općini.';
        }
    }

    if ($dogadanje['adresa'] !== null && mb_strlen($dogadanje['adresa']) > 200) {
        $greske['adresa'] = 'Adresa može imati najviše 200 znakova.';
    }

    if (trim($dogadanje['opis']) === '') {
        $greske['opis'] = 'Upiši opis događanja.';
    } elseif (mb_strlen($dogadanje['opis']) > NAJVECI_OPIS_DOGADANJA) {
        $greske['opis'] = 'Opis može imati najviše ' . NAJVECI_OPIS_DOGADANJA . ' znakova.';
    }

    if ($dogadanje['pocetak'] === null) {
        $greske['pocetak'] = 'Upiši datum i vrijeme početka.';
    } elseif ($dogadanje['pocetak'] === false) {
        $greske['pocetak'] = 'Upiši ispravan datum i vrijeme početka.';
    }

    if ($dogadanje['zavrsetak'] === false) {
        $greske['zavrsetak'] = 'Upiši ispravan datum i vrijeme završetka.';
    } elseif (is_string($dogadanje['zavrsetak']) && is_string($dogadanje['pocetak']) && $dogadanje['zavrsetak'] < $dogadanje['pocetak']) {
        $greske['zavrsetak'] = 'Završetak ne može biti prije početka.';
    }

    if ($dogadanje['cijena'] === false) {
        $greske['cijena'] = 'Cijena mora biti broj, npr. 12,50.';
    } elseif ($dogadanje['cijena'] !== null && ($dogadanje['cijena'] <= 0 || $dogadanje['cijena'] > NAJVECA_CIJENA)) {
        $greske['cijena'] = 'Cijena mora biti veća od 0 i najviše ' . NAJVECA_CIJENA . ' €. Za besplatan ulaz ostavi polje prazno.';
    }

    return $greske;
}

// Sprema događanje zajedno sa slikom u jednoj transakciji. Ako bilo što ne uspije, u bazi se ništa ne mijenja.
// $nova_slika su podaci iz prijenos_slike() ili null, a $ukloni_sliku je true ako je označeno Ukloni sliku.
function spremi_dogadanje($postojece, $dogadanje, $nova_slika, $ukloni_sliku, $autor_id)
{
    $stara_slika = $postojece !== null && $postojece['slika_id'] !== null ? slika_po_id($postojece['slika_id']) : null;
    $obrisi_staru = $stara_slika !== null && ($nova_slika !== null || $ukloni_sliku);

    $dogadanje['slika_id'] = $stara_slika['id'] ?? null;

    mysqli_begin_transaction(db());

    try {
        if ($nova_slika !== null) {
            $dogadanje['slika_id'] = slika_dodaj($nova_slika, $autor_id);
        } elseif ($ukloni_sliku) {
            $dogadanje['slika_id'] = null;
        }

        if ($postojece === null) {
            dogadanje_dodaj($dogadanje, $autor_id);
        } else {
            dogadanje_uredi($postojece['id'], $dogadanje);
        }

        if ($obrisi_staru) {
            slika_obrisi($stara_slika['id']);
        }

        mysqli_commit(db());
    } catch (Throwable $greska) {
        // Baza se vraća na stanje prije spremanja, a nova slika se briše iz mape prijenosi/
        mysqli_rollback(db());
        if ($nova_slika !== null) {
            obrisi_datoteku_slike($nova_slika['datoteka']);
        }
        throw $greska;
    }

    // Datoteka stare slike briše se tek kad je sve spremljeno u bazu
    if ($obrisi_staru) {
        obrisi_datoteku_slike($stara_slika['datoteka']);
    }
}

// Dodavanje događanja i uređivanje postojećeg (administrator i urednik, sva događanja)
function stranica_admin_dogadanje()
{
    $korisnik = zahtijevaj_ulogu(['administrator', 'urednik']);

    $postojece = null;
    $id = get_broj('id');

    if ($id > 0) {
        $postojece = dogadanje_po_id($id);

        if ($postojece === null) {
            stranica_nije_pronadena();
            return;
        }
    }

    $dogadanje = [
        'vrsta_id' => (int) ($postojece['vrsta_id'] ?? 0),
        'grad_opcina_id' => (int) ($postojece['grad_opcina_id'] ?? 0),
        'lokacija_id' => isset($postojece['lokacija_id']) ? (int) $postojece['lokacija_id'] : null,
        'naziv' => $postojece['naziv'] ?? '',
        'opis' => $postojece['opis'] ?? '',
        'adresa' => $postojece['adresa'] ?? null,
        'pocetak' => $postojece['pocetak'] ?? null,
        'zavrsetak' => $postojece['zavrsetak'] ?? null,
        'cijena' => isset($postojece['cijena']) ? (float) $postojece['cijena'] : null,
        'na_otvorenom' => (int) ($postojece['na_otvorenom'] ?? 0),
        'objavljeno' => (int) ($postojece['objavljeno'] ?? 0),
        'istaknuto' => (int) ($postojece['istaknuto'] ?? 0),
    ];
    $zupanija_id = (int) ($postojece['zupanija_id'] ?? 0);
    // Upisani tekst polja vremena i cijene, da se kod greške u obrascu vrati točno ono što je upisano
    $upisano = [];
    $greske = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $adresa = trim(post_tekst('adresa'));
        $upisano = ['pocetak' => post_tekst('pocetak'), 'zavrsetak' => post_tekst('zavrsetak'), 'cijena' => post_tekst('cijena')];

        $dogadanje = [
            'vrsta_id' => post_broj('vrsta'),
            'grad_opcina_id' => post_broj('grad_opcina'),
            'lokacija_id' => post_broj('lokacija') > 0 ? post_broj('lokacija') : null,
            'naziv' => trim(post_tekst('naziv')),
            'opis' => trim(post_tekst('opis')),
            'adresa' => $adresa !== '' ? $adresa : null,
            'pocetak' => datum_vrijeme_iz_obrasca($upisano['pocetak']),
            'zavrsetak' => datum_vrijeme_iz_obrasca($upisano['zavrsetak']),
            'cijena' => cijena_iz_obrasca($upisano['cijena']),
            'na_otvorenom' => post_tekst('na_otvorenom') === '1' ? 1 : 0,
            'objavljeno' => post_tekst('objavljeno') === '1' ? 1 : 0,
            'istaknuto' => post_tekst('istaknuto') === '1' ? 1 : 0,
        ];
        $zupanija_id = post_broj('zupanija');

        // Uz odabranu lokaciju grad/općina i županija ne moraju se birati, nego se uzimaju iz lokacije
        if ($dogadanje['lokacija_id'] !== null && $dogadanje['grad_opcina_id'] === 0) {
            $lokacija = lokacija_grad_opcina($dogadanje['lokacija_id']);

            if ($lokacija !== null) {
                $dogadanje['grad_opcina_id'] = (int) $lokacija['grad_opcina_id'];
                $zupanija_id = (int) grad_opcina_po_id($lokacija['grad_opcina_id'])['zupanija_id'];
            }
        }

        $greske = provjeri_dogadanje($dogadanje, $zupanija_id);

        // Slika se prenosi tek kad su ostala polja ispravna, inače bi u mapi prijenosi/ ostala slika bez događanja
        $nova_slika = null;
        $poslana_slika = ($_FILES['slika']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

        if (count($greske) === 0 && $poslana_slika) {
            [$nova_slika, $greska_slike] = prijenos_slike($_FILES['slika']);

            if ($greska_slike !== null) {
                $greske['slika'] = $greska_slike;
            }
        }

        if (count($greske) === 0) {
            spremi_dogadanje($postojece, $dogadanje, $nova_slika, post_tekst('ukloni_sliku') === '1', $korisnik['id']);

            $stanje = $dogadanje['objavljeno'] === 1 ? 'objavljeno' : 'spremljeno kao skriveno';
            postavi_poruku('uspjeh', 'Događanje „' . $dogadanje['naziv'] . '” je ' . $stanje . '.');
            preusmjeri('index.php?stranica=admin-dogadanja');
        }
    }

    prikazi('admin-dogadanje', [
        'postojece' => $postojece,
        'slika' => $postojece !== null && $postojece['slika_id'] !== null ? slika_po_id($postojece['slika_id']) : null,
        'dogadanje' => $dogadanje,
        'upisano' => $upisano,
        'greske' => $greske,
        'vrste' => vrste_dogadanja_sve(),
        'lokacije' => lokacije_za_odabir(),
        'zupanije' => zupanije_sve(),
        'gradovi_opcine' => gradovi_opcine_svi(),
        'odabrana_zupanija' => $zupanija_id,
        'odabrani_grad_opcina' => $dogadanje['grad_opcina_id'],
        'oznaka_sve_zupanije' => 'Odaberi županiju',
        'oznaka_svi_gradovi' => 'Odaberi grad ili općinu',
    ]);
}


// Brisanje događanja uz potvrdu, zajedno s njegovom slikom
function stranica_admin_dogadanje_obrisi()
{
    zahtijevaj_ulogu(['administrator', 'urednik']);

    $dogadanje = dogadanje_po_id(get_broj('id'));

    if ($dogadanje === null) {
        stranica_nije_pronadena();
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $slika = $dogadanje['slika_id'] !== null ? slika_po_id($dogadanje['slika_id']) : null;

        // Događanje i podaci o njegovoj slici brišu se zajedno ili se ne briše ništa
        mysqli_begin_transaction(db());

        try {
            dogadanje_obrisi($dogadanje['id']);

            if ($slika !== null) {
                slika_obrisi($slika['id']);
            }

            mysqli_commit(db());
        } catch (Throwable $greska) {
            mysqli_rollback(db());
            throw $greska;
        }

        // Datoteka slike briše se tek kad je brisanje u bazi uspjelo
        if ($slika !== null) {
            obrisi_datoteku_slike($slika['datoteka']);
        }

        postavi_poruku('uspjeh', 'Događanje „' . $dogadanje['naziv'] . '” je obrisano.');
        preusmjeri('index.php?stranica=admin-dogadanja');
    }

    prikazi('admin-dogadanje-obrisi', [
        'dogadanje' => $dogadanje,
    ]);
}
