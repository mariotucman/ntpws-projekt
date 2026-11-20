<?php
// Administracija lokacija. Vrste lokacija uređuje samo administrator.

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
