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

// Popis svih vijesti sa straničenjem
function stranica_admin_vijesti()
{
    zahtijevaj_ulogu(['administrator', 'urednik']);

    $ukupno = vijesti_admin_broj();
    $broj_stranica = max(1, (int) ceil($ukupno / ADMIN_VIJESTI_PO_STRANICI));
    $trenutna_stranica = min(max(1, get_broj('str')), $broj_stranica);

    prikazi('admin-vijesti', [
        'vijesti' => vijesti_admin(ADMIN_VIJESTI_PO_STRANICI, ($trenutna_stranica - 1) * ADMIN_VIJESTI_PO_STRANICI),
        'trenutna_stranica' => $trenutna_stranica,
        'broj_stranica' => $broj_stranica,
        'adresa' => ['stranica' => 'admin-vijesti'],
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

    if (trim(html_entity_decode(strip_tags($vijest['sadrzaj']))) === '') {
        $greske['sadrzaj'] = 'Upiši sadržaj vijesti.';
    } elseif (mb_strlen($vijest['sadrzaj']) > NAJVECI_SADRZAJ_VIJESTI) {
        $greske['sadrzaj'] = 'Sadržaj je predug.';
    }

    return $greske;
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
    }

    $vijest = [
        'kategorija_id' => (int) ($postojeca['kategorija_id'] ?? 0),
        'grad_opcina_id' => $postojeca['grad_opcina_id'] ?? null,
        'naslov' => $postojeca['naslov'] ?? '',
        'sazetak' => $postojeca['sazetak'] ?? '',
        'sadrzaj' => $postojeca['sadrzaj'] ?? '',
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
        ];
        $zupanija_id = post_broj('zupanija');
        $greske = provjeri_vijest($vijest, $zupanija_id);

        if (count($greske) === 0) {
            if ($postojeca === null) {
                vijest_dodaj($vijest, $korisnik['id']);
                postavi_poruku('uspjeh', 'Vijest „' . $vijest['naslov'] . '” je spremljena kao nacrt.');
            } else {
                vijest_uredi($id, $vijest);
                postavi_poruku('uspjeh', 'Vijest „' . $vijest['naslov'] . '” je spremljena.');
            }
            preusmjeri('index.php?stranica=admin-vijesti');
        }
    }

    prikazi('admin-vijest', [
        'postojeca' => $postojeca,
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
