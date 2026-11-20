<?php
$naslov = $postojeca === null ? 'Nova lokacija' : 'Uredi lokaciju';
$aktivno = 'administracija';
$admin_stavka = 'lokacije';

// Polje s greškom dobiva crveni obrub
$greska_klasa = function ($polje) use ($greske) {
    return isset($greske[$polje]) ? ' polje__unos--greska' : '';
};

// Koordinata za polje obrasca: neispravan unos (false) ostavlja polje prazno
$koordinata = function ($vrijednost) {
    return is_float($vrijednost) || is_string($vrijednost) ? (string) $vrijednost : '';
};
?>
<form class="obrazac-kartica obrazac-kartica--siroka" method="post" action="index.php?stranica=admin-lokacija<?= $postojeca !== null ? '&amp;id=' . e($postojeca['id']) : '' ?>" enctype="multipart/form-data" novalidate>
    <?= csrf_polje() ?>

    <?php if ($postojeca !== null): ?>
        <p class="polje__pomoc">
            Autor: <?= e($postojeca['autor']) ?>
            &middot; izrađeno <?= e(datum($postojeca['kreirano'], 'j. n. Y. \u H:i')) ?>
            &middot; zadnja izmjena <?= e(datum($postojeca['azurirano'], 'j. n. Y. \u H:i')) ?>
        </p>
    <?php endif; ?>

    <div class="polje">
        <label class="polje__oznaka" for="naziv">Naziv</label>
        <input class="polje__unos<?= $greska_klasa('naziv') ?>" type="text" id="naziv" name="naziv" value="<?= e($lokacija['naziv']) ?>" maxlength="150" required>
        <?php if (isset($greske['naziv'])): ?>
            <p class="polje__greska"><?= e($greske['naziv']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="vrsta">Vrsta</label>
        <select class="polje__unos<?= $greska_klasa('vrsta') ?>" id="vrsta" name="vrsta" required>
            <option value="">Odaberi vrstu</option>
            <?php foreach ($vrste as $vrsta): ?>
                <option value="<?= e($vrsta['id']) ?>"<?= (int) $vrsta['id'] === $lokacija['vrsta_id'] ? ' selected' : '' ?>><?= e($vrsta['naziv']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($greske['vrsta'])): ?>
            <p class="polje__greska"><?= e($greske['vrsta']) ?></p>
        <?php endif; ?>
    </div>

    <?php require __DIR__ . '/dijelovi/filtar-podrucja.php'; ?>
    <?php if (isset($greske['grad_opcina'])): ?>
        <p class="polje__greska"><?= e($greske['grad_opcina']) ?></p>
    <?php endif; ?>

    <div class="polje">
        <label class="polje__oznaka" for="opis">Opis</label>
        <textarea class="polje__unos<?= $greska_klasa('opis') ?>" id="opis" name="opis" rows="8" maxlength="<?= NAJVECI_OPIS_LOKACIJE ?>" required><?= e($lokacija['opis']) ?></textarea>
        <p class="polje__pomoc">Običan tekst. Novi red u opisu prikazuje se i na stranici lokacije.</p>
        <?php if (isset($greske['opis'])): ?>
            <p class="polje__greska"><?= e($greske['opis']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="adresa">Adresa (nije obavezna)</label>
        <input class="polje__unos<?= $greska_klasa('adresa') ?>" type="text" id="adresa" name="adresa" value="<?= e($lokacija['adresa']) ?>" maxlength="200">
        <p class="polje__pomoc">Ulica i broj, bez grada. Npr. Kaptol 31.</p>
        <?php if (isset($greske['adresa'])): ?>
            <p class="polje__greska"><?= e($greske['adresa']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="lat">Geografska širina</label>
        <input class="polje__unos<?= $greska_klasa('koordinate') ?>" type="text" id="lat" name="lat" value="<?= e($koordinata($lokacija['lat'])) ?>" inputmode="decimal">
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="lng">Geografska dužina</label>
        <input class="polje__unos<?= $greska_klasa('koordinate') ?>" type="text" id="lng" name="lng" value="<?= e($koordinata($lokacija['lng'])) ?>" inputmode="decimal">
        <p class="polje__pomoc">Npr. 45.814440 i 15.979720. Ako ostaviš prazno, lokacija dobiva koordinate središta grada ili općine.</p>
        <?php if (isset($greske['koordinate'])): ?>
            <p class="polje__greska"><?= e($greske['koordinate']) ?></p>
        <?php endif; ?>
    </div>

    <fieldset class="polje fotografije-unos">
        <legend class="polje__oznaka">Fotografije</legend>

        <?php foreach ($fotografije as $broj => $fotografija): ?>
            <div class="fotografije-unos__stavka">
                <img class="polje__slika" src="index.php?stranica=slika&amp;id=<?= e($fotografija['id']) ?>" width="<?= e($fotografija['sirina']) ?>" height="<?= e($fotografija['visina']) ?>" alt="Fotografija <?= $broj + 1 ?>: <?= e($fotografija['izvorni_naziv']) ?>" loading="lazy">
                <p class="polje__pomoc"><?= e($fotografija['izvorni_naziv']) ?></p>

                <label class="polje__oznaka" for="redoslijed-<?= e($fotografija['id']) ?>">Redoslijed</label>
                <input class="polje__unos" type="number" id="redoslijed-<?= e($fotografija['id']) ?>" name="redoslijed[<?= e($fotografija['id']) ?>]" value="<?= $broj + 1 ?>" min="1" max="<?= NAJVISE_FOTOGRAFIJA ?>">

                <label class="polje__kvacica">
                    <input type="checkbox" name="ukloni_fotografije[]" value="<?= e($fotografija['id']) ?>"<?= in_array((int) $fotografija['id'], $ukloni, true) ? ' checked' : '' ?>>
                    Ukloni
                </label>
            </div>
        <?php endforeach; ?>

        <label class="polje__oznaka" for="fotografije"><?= count($fotografije) > 0 ? 'Dodaj fotografije' : 'Odaberi fotografije' ?></label>
        <input class="polje__unos<?= $greska_klasa('fotografije') ?>" type="file" id="fotografije" name="fotografije[]" accept="image/jpeg,image/png,image/webp" multiple>
        <p class="polje__pomoc">
            Najviše <?= NAJVISE_FOTOGRAFIJA ?> fotografija, JPEG, PNG ili WebP do 5 MB svaka. Može se odabrati više odjednom.
            Prva po redoslijedu prikazuje se na kartici lokacije, a nove dolaze iza postojećih.
        </p>
        <?php if (isset($greske['fotografije'])): ?>
            <p class="polje__greska"><?= e($greske['fotografije']) ?></p>
        <?php endif; ?>
    </fieldset>

    <fieldset class="polje radno-vrijeme-unos">
        <legend class="polje__oznaka">Radno vrijeme</legend>

        <label class="polje__kvacica">
            <input type="checkbox" name="ima_radno_vrijeme" value="1"<?= $radno_vrijeme['ima'] ? ' checked' : '' ?>>
            Lokacija ima radno vrijeme
        </label>
        <p class="polje__pomoc">Bez ove kvačice lokacija je uvijek otvorena (npr. plaža, park ili vidikovac) i dani se ne spremaju.</p>

        <?php foreach (DANI_U_TJEDNU as $dan => $naziv_dana): ?>
            <?php [$od, $do, $zatvoreno] = $radno_vrijeme['dani'][$dan]; ?>
            <div class="radno-vrijeme-unos__dan">
                <span class="radno-vrijeme-unos__naziv"><?= e($naziv_dana) ?></span>

                <label for="rv-<?= e($dan) ?>-od">Od</label>
                <input class="polje__unos<?= isset($greske_radnog_vremena[$dan]) ? ' polje__unos--greska' : '' ?>" type="time" id="rv-<?= e($dan) ?>-od" name="radno_vrijeme[<?= e($dan) ?>][od]" value="<?= e($od) ?>">

                <label for="rv-<?= e($dan) ?>-do">Do</label>
                <input class="polje__unos<?= isset($greske_radnog_vremena[$dan]) ? ' polje__unos--greska' : '' ?>" type="time" id="rv-<?= e($dan) ?>-do" name="radno_vrijeme[<?= e($dan) ?>][do]" value="<?= e($do) ?>">

                <label class="polje__kvacica">
                    <input type="checkbox" name="radno_vrijeme[<?= e($dan) ?>][zatvoreno]" value="1"<?= $zatvoreno ? ' checked' : '' ?>>
                    Zatvoreno
                </label>
            </div>
            <?php if (isset($greske_radnog_vremena[$dan])): ?>
                <p class="polje__greska"><?= e($greske_radnog_vremena[$dan]) ?></p>
            <?php endif; ?>
        <?php endforeach; ?>

        <label class="polje__oznaka" for="rv-napomena">Napomena (nije obavezna)</label>
        <input class="polje__unos<?= isset($greske_radnog_vremena['napomena']) ? ' polje__unos--greska' : '' ?>" type="text" id="rv-napomena" name="radno_vrijeme[napomena]" value="<?= e($radno_vrijeme['napomena']) ?>" maxlength="200">
        <p class="polje__pomoc">Npr. „Ljeti do 20 h” ili „Zatvoreno od studenog do ožujka”.</p>
        <?php if (isset($greske_radnog_vremena['napomena'])): ?>
            <p class="polje__greska"><?= e($greske_radnog_vremena['napomena']) ?></p>
        <?php endif; ?>
    </fieldset>

    <div class="polje">
        <label class="polje__kvacica">
            <input type="checkbox" name="objavljeno" value="1"<?= $lokacija['objavljeno'] === 1 ? ' checked' : '' ?>>
            Objavljeno (lokacija se vidi javno)
        </label>
        <label class="polje__kvacica">
            <input type="checkbox" name="istaknuto" value="1"<?= $lokacija['istaknuto'] === 1 ? ' checked' : '' ?>>
            Istaknuto (prikazuje se na početnoj stranici)
        </label>
    </div>

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Spremi</button>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=admin-lokacije">Natrag na lokacije</a></p>
</form>
