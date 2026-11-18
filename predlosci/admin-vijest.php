<?php
$naslov = $postojeca === null ? 'Nova vijest' : 'Uredi vijest';
$aktivno = 'administracija';
$admin_stavka = 'vijesti';

// Polje s greškom dobiva crveni obrub
$greska_klasa = function ($polje) use ($greske) {
    return isset($greske[$polje]) ? ' polje__unos--greska' : '';
};
?>
<form class="obrazac-kartica obrazac-kartica--siroka" method="post" action="index.php?stranica=admin-vijest<?= $postojeca !== null ? '&amp;id=' . e($postojeca['id']) : '' ?>" enctype="multipart/form-data" novalidate>
    <?= csrf_polje() ?>

    <?php if ($postojeca === null): ?>
        <p class="polje__pomoc">Nova vijest se sprema kao nacrt i javno se ne prikazuje dok se ne objavi.</p>
    <?php endif; ?>

    <div class="polje">
        <label class="polje__oznaka" for="naslov">Naslov</label>
        <input class="polje__unos<?= $greska_klasa('naslov') ?>" type="text" id="naslov" name="naslov" value="<?= e($vijest['naslov']) ?>" maxlength="200" required>
        <?php if (isset($greske['naslov'])): ?>
            <p class="polje__greska" id="naslov-greska"><?= e($greske['naslov']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="sazetak">Sažetak</label>
        <textarea class="polje__unos<?= $greska_klasa('sazetak') ?>" id="sazetak" name="sazetak" rows="3" maxlength="400" required><?= e($vijest['sazetak']) ?></textarea>
        <?php if (isset($greske['sazetak'])): ?>
            <p class="polje__greska" id="sazetak-greska"><?= e($greske['sazetak']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="kategorija">Kategorija</label>
        <select class="polje__unos<?= $greska_klasa('kategorija') ?>" id="kategorija" name="kategorija" required>
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
        <p class="polje__greska"><?= e($greske['grad_opcina']) ?></p>
    <?php endif; ?>

    <div class="polje polje--uredivac">
        <label class="polje__oznaka" for="sadrzaj">Sadržaj</label>
        <p class="polje__pomoc" id="sadrzaj-pomoc">Odlomke odvoji praznim retkom.</p>
        <textarea class="polje__unos<?= $greska_klasa('sadrzaj') ?>" id="sadrzaj" name="sadrzaj" rows="15" required><?= e($vijest['sadrzaj']) ?></textarea>
        <?php if (isset($greske['sadrzaj'])): ?>
            <p class="polje__greska" id="sadrzaj-greska"><?= e($greske['sadrzaj']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="slika">Slika</label>

        <?php if ($slika !== null): ?>
            <img class="polje__slika" src="index.php?stranica=slika&amp;id=<?= e($slika['id']) ?>" width="<?= e($slika['sirina']) ?>" height="<?= e($slika['visina']) ?>" alt="Trenutna slika vijesti">
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

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Spremi</button>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=admin-vijesti">Natrag na vijesti</a></p>
</form>

<!-- Quill editor. Ako se ne učita, sadržaj se upisuje u obično tekstualno polje. -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" integrity="sha384-ecIckRi4QlKYya/FQUbBUjS4qp65jF/J87Guw5uzTbO1C1Jfa/6kYmd6dXUF6D7i" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js" integrity="sha384-utBUCeG4SYaCm4m7GQZYr8Hy8Fpy3V4KGjBZaf4WTKOcwhCYpt/0PfeEe3HNlwx8" crossorigin="anonymous" defer></script>
<script src="js/uredivac-vijesti.js" defer></script>
