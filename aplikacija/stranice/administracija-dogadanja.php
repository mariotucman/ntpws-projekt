<?php
// Administracija događanja. Događanja uređuju administrator i urednik, a vrste događanja samo administrator.

const ADMIN_DOGADANJA_PO_STRANICI = 20;

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

