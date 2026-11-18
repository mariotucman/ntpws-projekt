<?php
$naslov = $postojeca === null ? 'Nova vijest' : 'Uredi vijest';
$aktivno = 'administracija';
$admin_stavka = 'vijesti';

// Atributi polja s greškom: aria-invalid i poveznica na poruku greške
$greska_atributi = function ($polje) use ($greske) {
    return isset($greske[$polje]) ? ' aria-invalid="true" aria-describedby="' . $polje . '-greska"' : '';
};
?>
<form class="obrazac-kartica obrazac-kartica--siroka" method="post" action="index.php?stranica=admin-vijest<?= $postojeca !== null ? '&amp;id=' . e($postojeca['id']) : '' ?>" novalidate>
    <?= csrf_polje() ?>

    <?php if ($postojeca === null): ?>
        <p class="polje__pomoc">Nova vijest se sprema kao nacrt i javno se ne prikazuje dok se ne objavi.</p>
    <?php endif; ?>

    <div class="polje">
        <label class="polje__oznaka" for="naslov">Naslov</label>
        <input class="polje__unos" type="text" id="naslov" name="naslov" value="<?= e($vijest['naslov']) ?>" maxlength="200" required<?= $greska_atributi('naslov') ?>>
        <?php if (isset($greske['naslov'])): ?>
            <p class="polje__greska" id="naslov-greska"><?= e($greske['naslov']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="sazetak">Sažetak</label>
        <textarea class="polje__unos" id="sazetak" name="sazetak" rows="3" maxlength="400" required<?= $greska_atributi('sazetak') ?>><?= e($vijest['sazetak']) ?></textarea>
        <?php if (isset($greske['sazetak'])): ?>
            <p class="polje__greska" id="sazetak-greska"><?= e($greske['sazetak']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="kategorija">Kategorija</label>
        <select class="polje__unos" id="kategorija" name="kategorija" required<?= $greska_atributi('kategorija') ?>>
            <option value="">Odaberi kategoriju</option>
            <?php foreach ($kategorije as $kategorija): ?>
                <option value="<?= e($kategorija['id']) ?>"<?= (int) $kategorija['id'] === $vijest['kategorija_id'] ? ' selected' : '' ?>><?= e($kategorija['naziv']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($greske['kategorija'])): ?>
            <p class="polje__greska" id="kategorija-greska"><?= e($greske['kategorija']) ?></p>
        <?php endif; ?>
    </div>

    <?php require __DIR__ . '/dijelovi/filtar-podrucja.php'; ?>
    <?php if (isset($greske['grad_opcina'])): ?>
        <p class="polje__greska" role="alert"><?= e($greske['grad_opcina']) ?></p>
    <?php endif; ?>

    <div class="polje">
        <label class="polje__oznaka" for="sadrzaj">Sadržaj</label>
        <p class="polje__pomoc" id="sadrzaj-pomoc">Odlomke odvoji praznim retkom.</p>
        <textarea class="polje__unos" id="sadrzaj" name="sadrzaj" rows="15" required aria-describedby="sadrzaj-pomoc<?= isset($greske['sadrzaj']) ? ' sadrzaj-greska' : '' ?>"<?= isset($greske['sadrzaj']) ? ' aria-invalid="true"' : '' ?>><?= e($vijest['sadrzaj']) ?></textarea>
        <?php if (isset($greske['sadrzaj'])): ?>
            <p class="polje__greska" id="sadrzaj-greska"><?= e($greske['sadrzaj']) ?></p>
        <?php endif; ?>
    </div>

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Spremi</button>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=admin-vijesti">Natrag na vijesti</a></p>
</form>

<!-- Quill editor. Ako se ne učita, sadržaj se upisuje u obično tekstualno polje. -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" integrity="sha384-ecIckRi4QlKYya/FQUbBUjS4qp65jF/J87Guw5uzTbO1C1Jfa/6kYmd6dXUF6D7i" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js" integrity="sha384-utBUCeG4SYaCm4m7GQZYr8Hy8Fpy3V4KGjBZaf4WTKOcwhCYpt/0PfeEe3HNlwx8" crossorigin="anonymous" defer></script>
<script src="js/uredivac-vijesti.js" defer></script>
