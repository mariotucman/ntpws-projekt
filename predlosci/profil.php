<?php
$naslov = 'Profil';
$opis = $korisnik['uloga_naziv'] . ' od ' . date('j. n. Y.', strtotime($korisnik['kreirano']));
$aktivno = 'profil';
?>
<form class="obrazac-kartica" method="post" action="index.php?stranica=profil" novalidate>
    <?= csrf_polje() ?>
    <h2 class="obrazac-kartica__naslov">Osobni podaci</h2>

    <div class="polje">
        <label class="polje__oznaka" for="ime">Ime</label>
        <input class="polje__unos" type="text" id="ime" name="ime" value="<?= e($ime) ?>" autocomplete="given-name" required
            <?= isset($greske['ime']) ? 'aria-invalid="true" aria-describedby="ime-greska"' : '' ?>>
        <?php if (isset($greske['ime'])): ?>
            <p class="polje__greska" id="ime-greska"><?= e($greske['ime']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="email">Email</label>
        <input class="polje__unos" type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="email" required
            <?= isset($greske['email']) ? 'aria-invalid="true" aria-describedby="email-greska"' : '' ?>>
        <?php if (isset($greske['email'])): ?>
            <p class="polje__greska" id="email-greska"><?= e($greske['email']) ?></p>
        <?php endif; ?>
    </div>

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Spremi promjene</button>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=lozinka">Promijeni lozinku</a></p>
</form>
