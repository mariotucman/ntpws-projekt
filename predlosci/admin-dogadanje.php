<?php
$naslov = $postojece === null ? 'Novo događanje' : 'Uredi događanje';
$aktivno = 'administracija';
$admin_stavka = 'dogadanja';

// Polje s greškom dobiva crveni obrub
$greska_klasa = function ($polje) use ($greske) {
    return isset($greske[$polje]) ? ' polje__unos--greska' : '';
};

// Vrijednost polja datetime-local: upisani tekst nakon greške, inače vrijednost iz baze (2026-12-18T19:30)
$vrijeme = function ($polje) use ($upisano, $dogadanje) {
    if (isset($upisano[$polje])) {
        return $upisano[$polje];
    }

    return is_string($dogadanje[$polje]) ? datum($dogadanje[$polje], 'Y-m-d\TH:i') : '';
};

// Cijena za polje: upisani tekst nakon greške, inače iznos s decimalnim zarezom (12,50)
$cijena_polje = $upisano['cijena'] ?? ($dogadanje['cijena'] !== null ? number_format($dogadanje['cijena'], 2, ',', '') : '');

// Lokacije grupirane po gradu/općini za padajući izbornik
$lokacije_po_gradu = [];
foreach ($lokacije as $lokacija) {
    $lokacije_po_gradu[$lokacija['grad_opcina']][] = $lokacija;
}
?>
<form class="obrazac-kartica obrazac-kartica--siroka" method="post" action="index.php?stranica=admin-dogadanje<?= $postojece !== null ? '&amp;id=' . e($postojece['id']) : '' ?>" enctype="multipart/form-data" novalidate>
    <?= csrf_polje() ?>

    <?php if ($postojece !== null): ?>
        <p class="polje__pomoc">
            Autor: <?= e($postojece['autor']) ?>
            &middot; izrađeno <?= e(datum($postojece['kreirano'], 'j. n. Y. \u H:i')) ?>
            &middot; zadnja izmjena <?= e(datum($postojece['azurirano'], 'j. n. Y. \u H:i')) ?>
        </p>
    <?php endif; ?>

    <div class="polje">
        <label class="polje__oznaka" for="naziv">Naziv</label>
        <input class="polje__unos<?= $greska_klasa('naziv') ?>" type="text" id="naziv" name="naziv" value="<?= e($dogadanje['naziv']) ?>" maxlength="150" required>
        <?php if (isset($greske['naziv'])): ?>
            <p class="polje__greska"><?= e($greske['naziv']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="vrsta">Vrsta</label>
        <select class="polje__unos<?= $greska_klasa('vrsta') ?>" id="vrsta" name="vrsta" required>
            <option value="">Odaberi vrstu</option>
            <?php foreach ($vrste as $vrsta): ?>
                <option value="<?= e($vrsta['id']) ?>"<?= (int) $vrsta['id'] === $dogadanje['vrsta_id'] ? ' selected' : '' ?>><?= e($vrsta['naziv']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($greske['vrsta'])): ?>
            <p class="polje__greska"><?= e($greske['vrsta']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="lokacija">Lokacija (nije obavezna)</label>
        <select class="polje__unos<?= $greska_klasa('lokacija') ?>" id="lokacija" name="lokacija">
            <option value="">Bez lokacije iz baze</option>
            <?php foreach ($lokacije_po_gradu as $grad => $lokacije_grada): ?>
                <optgroup label="<?= e($grad) ?>">
                    <?php foreach ($lokacije_grada as $lokacija): ?>
                        <option value="<?= e($lokacija['id']) ?>"<?= (int) $lokacija['id'] === $dogadanje['lokacija_id'] ? ' selected' : '' ?>><?= e($lokacija['naziv']) ?></option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
        <p class="polje__pomoc">Mjesto održavanja ako je to lokacija iz baze (npr. Arena). Ako odabereš lokaciju, grad/općina se postavlja sam.</p>
        <?php if (isset($greske['lokacija'])): ?>
            <p class="polje__greska"><?= e($greske['lokacija']) ?></p>
        <?php endif; ?>
    </div>

    <!-- Županija i grad/općina biraju se samo za događanja bez lokacije iz baze -->
    <?php require __DIR__ . '/dijelovi/filtar-podrucja.php'; ?>
    <?php if (isset($greske['grad_opcina'])): ?>
        <p class="polje__greska"><?= e($greske['grad_opcina']) ?></p>
    <?php endif; ?>

    <div class="polje">
        <label class="polje__oznaka" for="adresa">Adresa (nije obavezna)</label>
        <input class="polje__unos<?= $greska_klasa('adresa') ?>" type="text" id="adresa" name="adresa" value="<?= e($dogadanje['adresa']) ?>" maxlength="200">
        <p class="polje__pomoc">Ulica, trg ili dvorana, bez grada. Npr. Trg bana Josipa Jelačića.</p>
        <?php if (isset($greske['adresa'])): ?>
            <p class="polje__greska"><?= e($greske['adresa']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="opis">Opis</label>
        <textarea class="polje__unos<?= $greska_klasa('opis') ?>" id="opis" name="opis" rows="6" maxlength="<?= NAJVECI_OPIS_DOGADANJA ?>" required><?= e($dogadanje['opis']) ?></textarea>
        <p class="polje__pomoc">Običan tekst. Novi red u opisu prikazuje se i na stranici događanja.</p>
        <?php if (isset($greske['opis'])): ?>
            <p class="polje__greska"><?= e($greske['opis']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="pocetak">Početak</label>
        <input class="polje__unos<?= $greska_klasa('pocetak') ?>" type="datetime-local" id="pocetak" name="pocetak" value="<?= e($vrijeme('pocetak')) ?>" required>
        <?php if (isset($greske['pocetak'])): ?>
            <p class="polje__greska"><?= e($greske['pocetak']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="zavrsetak">Završetak (nije obavezan)</label>
        <input class="polje__unos<?= $greska_klasa('zavrsetak') ?>" type="datetime-local" id="zavrsetak" name="zavrsetak" value="<?= e($vrijeme('zavrsetak')) ?>">
        <p class="polje__pomoc">Za događanja kroz više dana (npr. festival) ili kad je poznato vrijeme kraja.</p>
        <?php if (isset($greske['zavrsetak'])): ?>
            <p class="polje__greska"><?= e($greske['zavrsetak']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="cijena">Cijena ulaznice u eurima (nije obavezna)</label>
        <input class="polje__unos<?= $greska_klasa('cijena') ?>" type="text" id="cijena" name="cijena" value="<?= e($cijena_polje) ?>" inputmode="decimal">
        <p class="polje__pomoc">Npr. 12,50. Prazno znači besplatan ulaz.</p>
        <?php if (isset($greske['cijena'])): ?>
            <p class="polje__greska"><?= e($greske['cijena']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="slika">Slika (nije obavezna)</label>

        <?php if ($slika !== null): ?>
            <img class="polje__slika" src="index.php?stranica=slika&amp;id=<?= e($slika['id']) ?>" width="<?= e($slika['sirina']) ?>" height="<?= e($slika['visina']) ?>" alt="Trenutna slika događanja">
            <p class="polje__pomoc">Trenutna slika: <?= e($slika['izvorni_naziv']) ?></p>
            <label class="polje__kvacica">
                <input type="checkbox" name="ukloni_sliku" value="1">
                Ukloni sliku
            </label>
        <?php endif; ?>

        <input class="polje__unos<?= $greska_klasa('slika') ?>" type="file" id="slika" name="slika" accept="image/jpeg,image/png,image/webp">
        <p class="polje__pomoc">JPEG, PNG ili WebP, najviše 5 MB.<?= $slika !== null ? ' Nova slika zamjenjuje trenutnu.' : '' ?></p>
        <?php if (isset($greske['slika'])): ?>
            <p class="polje__greska"><?= e($greske['slika']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__kvacica">
            <input type="checkbox" name="na_otvorenom" value="1"<?= $dogadanje['na_otvorenom'] === 1 ? ' checked' : '' ?>>
            Na otvorenom (prikazuje se prognoza)
        </label>
        <label class="polje__kvacica">
            <input type="checkbox" name="objavljeno" value="1"<?= $dogadanje['objavljeno'] === 1 ? ' checked' : '' ?>>
            Objavljeno (događanje se vidi javno)
        </label>
        <label class="polje__kvacica">
            <input type="checkbox" name="istaknuto" value="1"<?= $dogadanje['istaknuto'] === 1 ? ' checked' : '' ?>>
            Istaknuto (prikazuje se na početnoj stranici)
        </label>
    </div>

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Spremi</button>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=admin-dogadanja">Natrag na događanja</a></p>
</form>
