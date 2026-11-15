<?php
$naslov = 'Promjena lozinke';
$opis = 'Za promjenu upiši trenutnu lozinku, a zatim dvaput novu.';
$aktivno = 'profil';
?>
<form class="obrazac-kartica" method="post" action="index.php?stranica=lozinka" novalidate>
    <?= csrf_polje() ?>
    <h2 class="obrazac-kartica__naslov">Nova lozinka</h2>

    <div class="polje">
        <label class="polje__oznaka" for="trenutna">Trenutna lozinka</label>
        <input class="polje__unos" type="password" id="trenutna" name="trenutna" autocomplete="current-password" required
            <?= isset($greske['trenutna']) ? 'aria-invalid="true" aria-describedby="trenutna-greska"' : '' ?>>
        <?php if (isset($greske['trenutna'])): ?>
            <p class="polje__greska" id="trenutna-greska"><?= e($greske['trenutna']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="lozinka">Nova lozinka</label>
        <input class="polje__unos" type="password" id="lozinka" name="lozinka" autocomplete="new-password" required
            aria-describedby="lozinka-pomoc<?= isset($greske['lozinka']) ? ' lozinka-greska' : '' ?>"
            <?= isset($greske['lozinka']) ? 'aria-invalid="true"' : '' ?>>
        <p class="polje__pomoc" id="lozinka-pomoc">Najmanje 8 znakova.</p>
        <?php if (isset($greske['lozinka'])): ?>
            <p class="polje__greska" id="lozinka-greska"><?= e($greske['lozinka']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="lozinka_ponovo">Ponovi novu lozinku</label>
        <input class="polje__unos" type="password" id="lozinka_ponovo" name="lozinka_ponovo" autocomplete="new-password" required
            <?= isset($greske['lozinka_ponovo']) ? 'aria-invalid="true" aria-describedby="lozinka_ponovo-greska"' : '' ?>>
        <?php if (isset($greske['lozinka_ponovo'])): ?>
            <p class="polje__greska" id="lozinka_ponovo-greska"><?= e($greske['lozinka_ponovo']) ?></p>
        <?php endif; ?>
    </div>

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Promijeni lozinku</button>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=profil">Natrag na profil</a></p>
</form>
