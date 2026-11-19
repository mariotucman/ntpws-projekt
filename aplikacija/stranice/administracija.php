<?php
// Administracija. Vijesti uređuju administrator i urednik, a kategorije samo administrator.

const ADMIN_VIJESTI_PO_STRANICI = 20;

// Najveća duljina sadržaja vijesti (HTML) u znakovima
const NAJVECI_SADRZAJ_VIJESTI = 100000;

// Nazivi statusa vijesti za prikaz
const STATUSI_VIJESTI = [
    'nacrt' => 'Nacrt',
    'objavljeno' => 'Objavljeno',
    'zakazano' => 'Zakazano',
    'povuceno' => 'Povučeno',
];

function provjeri_kategoriju($naziv, $osim_id = 0)
{
    $greske = [];

    if ($naziv === '') {
        $greske['naziv'] = 'Upiši naziv kategorije.';
    } elseif (mb_strlen($naziv) > 60) {
        $greske['naziv'] = 'Naziv može imati najviše 60 znakova.';
    } elseif (kategorija_naziv_zauzet($naziv, $osim_id)) {
        $greske['naziv'] = 'Kategorija s tim nazivom već postoji.';
    }

    return $greske;
}

// Popis kategorija vijesti
function stranica_admin_kategorije()
{
    zahtijevaj_ulogu(['administrator']);

    prikazi('admin-kategorije', [
        'kategorije' => kategorije_s_brojem_vijesti(),
    ]);
}

// Dodavanje kategorije i uređivanje postojeće
function stranica_admin_kategorija()
{
    zahtijevaj_ulogu(['administrator']);

    $kategorija = null;
    $id = get_broj('id');

    if ($id > 0) {
        $kategorija = kategorija_po_id($id);

        if ($kategorija === null) {
            stranica_nije_pronadena();
            return;
        }
    }

    $naziv = $kategorija['naziv'] ?? '';
    $greske = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $naziv = trim(post_tekst('naziv'));
        $greske = provjeri_kategoriju($naziv, $id);

        if (count($greske) === 0) {
            if ($kategorija === null) {
                kategorija_dodaj($naziv);
                postavi_poruku('uspjeh', 'Kategorija „' . $naziv . '” je dodana.');
            } else {
                kategorija_uredi($id, $naziv);
                postavi_poruku('uspjeh', 'Kategorija „' . $naziv . '” je spremljena.');
            }
            preusmjeri('index.php?stranica=admin-kategorije');
        }
    }

    prikazi('admin-kategorija', [
        'kategorija' => $kategorija,
        'naziv' => $naziv,
        'greske' => $greske,
    ]);
}

// Brisanje kategorije
function stranica_admin_kategorija_obrisi()
{
    zahtijevaj_ulogu(['administrator']);

    $kategorija = kategorija_po_id(get_broj('id'));

    if ($kategorija === null) {
        stranica_nije_pronadena();
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (kategorija_obrisi($kategorija['id'])) {
            postavi_poruku('uspjeh', 'Kategorija „' . $kategorija['naziv'] . '” je obrisana.');
        } else {
            postavi_poruku('greska', 'Kategorija „' . $kategorija['naziv'] . '” se ne može obrisati jer je koriste vijesti.');
        }
        preusmjeri('index.php?stranica=admin-kategorije');
    }

    prikazi('admin-kategorija-obrisi', [
        'kategorija' => $kategorija,
        'broj_vijesti' => kategorija_broj_vijesti($kategorija['id']),
    ]);
}

// Popis svih vijesti s pretragom i straničenjem
function stranica_admin_vijesti()
{
    $korisnik = zahtijevaj_ulogu(['administrator', 'urednik']);

    $trazi = get_tekst('trazi');
    // Administrator vidi sve vijesti, a urednik samo svoje
    $autor_id = $korisnik['uloga'] === 'administrator' ? 0 : $korisnik['id'];

    $ukupno = vijesti_admin_broj($trazi, $autor_id);
    $broj_stranica = max(1, (int) ceil($ukupno / ADMIN_VIJESTI_PO_STRANICI));
    $trenutna_stranica = min(max(1, get_broj('str')), $broj_stranica);

    prikazi('admin-vijesti', [
        'vijesti' => vijesti_admin($trazi, $autor_id, ADMIN_VIJESTI_PO_STRANICI, ($trenutna_stranica - 1) * ADMIN_VIJESTI_PO_STRANICI),
        'trazi' => $trazi,
        'trenutna_stranica' => $trenutna_stranica,
        'broj_stranica' => $broj_stranica,
        // Poveznice straničenja zadržavaju pretragu
        'adresa' => array_filter(['stranica' => 'admin-vijesti', 'trazi' => $trazi]),
    ]);
}

// Običan tekst (obrazac bez JavaScripta) pretvara u HTML odlomke.
// Prazan redak odvaja odlomke, a novi red unutar odlomka postaje <br>.
function tekst_u_odlomke($tekst)
{
    $odlomci = preg_split('/\R\s*\R/u', trim($tekst));
    $html = '';

    foreach ($odlomci as $odlomak) {
        if (trim($odlomak) !== '') {
            $html .= '<p>' . nl2br(e(trim($odlomak)), false) . '</p>';
        }
    }

    return $html;
}

// Provjera podataka vijesti. Vraća polje grešaka po nazivu polja.
function provjeri_vijest($vijest, $zupanija_id)
{
    $greske = [];

    if ($vijest['naslov'] === '') {
        $greske['naslov'] = 'Upiši naslov vijesti.';
    } elseif (mb_strlen($vijest['naslov']) > 200) {
        $greske['naslov'] = 'Naslov može imati najviše 200 znakova.';
    }

    if ($vijest['sazetak'] === '') {
        $greske['sazetak'] = 'Upiši sažetak vijesti.';
    } elseif (mb_strlen($vijest['sazetak']) > 400) {
        $greske['sazetak'] = 'Sažetak može imati najviše 400 znakova.';
    }

    if (kategorija_po_id($vijest['kategorija_id']) === null) {
        $greske['kategorija'] = 'Odaberi kategoriju.';
    }

    // Vijest je za jedan grad/općinu ili za cijelu Hrvatsku, a ne za cijelu županiju
    if ($vijest['grad_opcina_id'] === null) {
        if ($zupanija_id > 0) {
            $greske['grad_opcina'] = 'Odaberi grad ili općinu, ili za županiju odaberi „Cijela Hrvatska”.';
        }
    } else {
        $grad_opcina = grad_opcina_po_id($vijest['grad_opcina_id']);

        if ($grad_opcina === null || ($zupanija_id > 0 && (int) $grad_opcina['zupanija_id'] !== $zupanija_id)) {
            $greske['grad_opcina'] = 'Odabrani grad ili općina nije u odabranoj županiji.';
        }
    }

    if (!in_array($vijest['status'], ['nacrt', 'objavljeno', 'povuceno'], true)) {
        $greske['status'] = 'Odaberi status vijesti.';
    }

    if ($vijest['objavljeno_u'] === false) {
        $greske['objavljeno_u'] = 'Upiši ispravan datum i vrijeme objave.';
    } elseif ($vijest['status'] === 'povuceno' && strtotime($vijest['objavljeno_u']) > time()) {
        $greske['objavljeno_u'] = 'Povučena vijest ne može imati datum objave u budućnosti.';
    }

    if (trim(html_entity_decode(strip_tags($vijest['sadrzaj']))) === '') {
        $greske['sadrzaj'] = 'Upiši sadržaj vijesti.';
    } elseif (mb_strlen($vijest['sadrzaj']) > NAJVECI_SADRZAJ_VIJESTI) {
        $greske['sadrzaj'] = 'Sadržaj je predug.';
    }

    return $greske;
}

// Sprema vijest zajedno sa slikom u jednoj transakciji. Ako bilo što ne uspije, u bazi se ništa ne mijenja.
// $nova_slika su podaci iz prijenos_slike() ili null, a $ukloni_sliku je true ako je označeno Ukloni sliku.
function spremi_vijest($postojeca, $vijest, $nova_slika, $ukloni_sliku, $autor_id)
{
    $stara_slika = $postojeca !== null && $postojeca['slika_id'] !== null ? slika_po_id($postojeca['slika_id']) : null;
    $obrisi_staru = $stara_slika !== null && ($nova_slika !== null || $ukloni_sliku);

    $vijest['slika_id'] = $stara_slika['id'] ?? null;

    mysqli_begin_transaction(db());

    try {
        if ($nova_slika !== null) {
            $vijest['slika_id'] = slika_dodaj($nova_slika, $autor_id);
        } elseif ($ukloni_sliku) {
            $vijest['slika_id'] = null;
        }

        if ($postojeca === null) {
            vijest_dodaj($vijest, $autor_id);
        } else {
            vijest_uredi($postojeca['id'], $vijest);
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

// Datum objave iz obrasca (polje datetime-local, npr. 2026-11-20T09:30) u obliku za bazu.
// Nacrt nema datum objave. Ostali statusi uz prazno polje dobivaju trenutno vrijeme.
function datum_objave($tekst, $status)
{
    if ($status === 'nacrt') {
        return null;
    }

    if ($tekst === '') {
        return date('Y-m-d H:i:s');
    }

    $datum = date_create_from_format('Y-m-d\TH:i', $tekst);

    // Nepostojeći datum (npr. 31. 2.)
    if ($datum === false || date_get_last_errors() !== false) {
        return false;
    }

    return date_format($datum, 'Y-m-d H:i:00');
}

// Poruka nakon spremanja, prema statusu vijesti
function poruka_spremanja_vijesti($vijest)
{
    $naslov = '„' . $vijest['naslov'] . '”';

    if ($vijest['status'] === 'nacrt') {
        return 'Vijest ' . $naslov . ' je spremljena kao nacrt.';
    }

    if ($vijest['status'] === 'povuceno') {
        return 'Vijest ' . $naslov . ' je povučena iz javnog prikaza.';
    }

    if (strtotime($vijest['objavljeno_u']) > time()) {
        return 'Vijest ' . $naslov . ' će biti objavljena ' . datum($vijest['objavljeno_u'], 'j. n. Y. \u H:i') . '.';
    }

    return 'Vijest ' . $naslov . ' je objavljena.';
}

// Dodavanje vijesti i uređivanje postojeće
function stranica_admin_vijest()
{
    $korisnik = zahtijevaj_ulogu(['administrator', 'urednik']);

    $postojeca = null;
    $id = get_broj('id');

    if ($id > 0) {
        $postojeca = vijest_po_id($id);

        if ($postojeca === null) {
            stranica_nije_pronadena();
            return;
        }

        if (!smije_urediti_vijest($postojeca)) {
            stranica_zabranjeno('Možeš uređivati samo svoje vijesti.');
            return;
        }
    }

    $vijest = [
        'kategorija_id' => (int) ($postojeca['kategorija_id'] ?? 0),
        'grad_opcina_id' => $postojeca['grad_opcina_id'] ?? null,
        'naslov' => $postojeca['naslov'] ?? '',
        'sazetak' => $postojeca['sazetak'] ?? '',
        'sadrzaj' => $postojeca['sadrzaj'] ?? '',
        'status' => $postojeca['status'] ?? 'nacrt',
        'objavljeno_u' => $postojeca['objavljeno_u'] ?? null,
    ];
    $zupanija_id = (int) ($postojeca['zupanija_id'] ?? 0);
    $greske = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $sadrzaj = post_tekst('sadrzaj');

        // Bez oznaka znači da je poslan običan tekst, pa se pretvara u odlomke
        if (!str_contains($sadrzaj, '<')) {
            $sadrzaj = tekst_u_odlomke($sadrzaj);
        }

        $vijest = [
            'kategorija_id' => post_broj('kategorija'),
            'grad_opcina_id' => post_broj('grad_opcina') > 0 ? post_broj('grad_opcina') : null,
            'naslov' => trim(post_tekst('naslov')),
            'sazetak' => trim(post_tekst('sazetak')),
            'sadrzaj' => ocisti_html($sadrzaj),
            'status' => post_tekst('status'),
            'objavljeno_u' => datum_objave(trim(post_tekst('objavljeno_u')), post_tekst('status')),
        ];
        $zupanija_id = post_broj('zupanija');
        $greske = provjeri_vijest($vijest, $zupanija_id);

        // Slika se prenosi tek kad su ostala polja ispravna, inače bi u mapi prijenosi/ ostala slika bez vijesti
        $nova_slika = null;
        $poslana_slika = ($_FILES['slika']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;

        if (count($greske) === 0 && $poslana_slika) {
            [$nova_slika, $greska_slike] = prijenos_slike($_FILES['slika']);

            if ($greska_slike !== null) {
                $greske['slika'] = $greska_slike;
            }
        }

        if (count($greske) === 0) {
            spremi_vijest($postojeca, $vijest, $nova_slika, post_tekst('ukloni_sliku') === '1', $korisnik['id']);

            postavi_poruku('uspjeh', poruka_spremanja_vijesti($vijest));
            preusmjeri('index.php?stranica=admin-vijesti');
        }
    }

    prikazi('admin-vijest', [
        'postojeca' => $postojeca,
        'slika' => $postojeca !== null && $postojeca['slika_id'] !== null ? slika_po_id($postojeca['slika_id']) : null,
        'vijest' => $vijest,
        'greske' => $greske,
        'kategorije' => kategorije_sve(),
        'zupanije' => zupanije_sve(),
        'gradovi_opcine' => gradovi_opcine_svi(),
        'odabrana_zupanija' => $zupanija_id,
        'odabrani_grad_opcina' => (int) $vijest['grad_opcina_id'],
        'oznaka_sve_zupanije' => 'Cijela Hrvatska',
        'oznaka_svi_gradovi' => 'Odaberi grad ili općinu',
    ]);
}

// Brisanje vijesti uz potvrdu. Briše se i slika vijesti.
function stranica_admin_vijest_obrisi()
{
    zahtijevaj_ulogu(['administrator', 'urednik']);

    $vijest = vijest_po_id(get_broj('id'));

    if ($vijest === null) {
        stranica_nije_pronadena();
        return;
    }

    if (!smije_urediti_vijest($vijest)) {
        stranica_zabranjeno('Možeš brisati samo svoje vijesti.');
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $slika = $vijest['slika_id'] !== null ? slika_po_id($vijest['slika_id']) : null;

        // Vijest i podaci o njezinoj slici brišu se zajedno ili se ne briše ništa
        mysqli_begin_transaction(db());

        try {
            vijest_obrisi($vijest['id']);

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

        postavi_poruku('uspjeh', 'Vijest „' . $vijest['naslov'] . '” je obrisana.');
        preusmjeri('index.php?stranica=admin-vijesti');
    }

    prikazi('admin-vijest-obrisi', [
        'vijest' => $vijest,
    ]);
}
