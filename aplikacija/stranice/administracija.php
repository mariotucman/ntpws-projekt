<?php
// Administracija. Sve stranice su samo za administratora.

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
