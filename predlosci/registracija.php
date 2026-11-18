<?php
$naslov = 'Registracija';
$opis = 'Napravi račun i spremaj lokacije, piši recenzije i složi svoj plan puta.';
$aktivno = 'registracija';
?>
<form class="obrazac-kartica" method="post" action="index.php?stranica=registracija" novalidate>
    <?= csrf_polje() ?>
    <h2 class="obrazac-kartica__naslov">Napravi račun</h2>

    <div class="polje">
        <label class="polje__oznaka" for="ime">Ime</label>
        <input class="polje__unos<?= isset($greske['ime']) ? ' polje__unos--greska' : '' ?>" type="text" id="ime" name="ime" value="<?= e($ime) ?>" autocomplete="given-name" required>
        <?php if (isset($greske['ime'])): ?>
            <p class="polje__greska" id="ime-greska"><?= e($greske['ime']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="email">Email</label>
        <input class="polje__unos<?= isset($greske['email']) ? ' polje__unos--greska' : '' ?>" type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="email" required>
        <?php if (isset($greske['email'])): ?>
            <p class="polje__greska" id="email-greska"><?= e($greske['email']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="lozinka">Lozinka</label>
        <input class="polje__unos<?= isset($greske['lozinka']) ? ' polje__unos--greska' : '' ?>" type="password" id="lozinka" name="lozinka" autocomplete="new-password" required>
        <p class="polje__pomoc" id="lozinka-pomoc">Najmanje 8 znakova.</p>
        <?php if (isset($greske['lozinka'])): ?>
            <p class="polje__greska" id="lozinka-greska"><?= e($greske['lozinka']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="lozinka_ponovo">Ponovi lozinku</label>
        <input class="polje__unos<?= isset($greske['lozinka_ponovo']) ? ' polje__unos--greska' : '' ?>" type="password" id="lozinka_ponovo" name="lozinka_ponovo" autocomplete="new-password" required>
        <?php if (isset($greske['lozinka_ponovo'])): ?>
            <p class="polje__greska" id="lozinka_ponovo-greska"><?= e($greske['lozinka_ponovo']) ?></p>
        <?php endif; ?>
    </div>

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Registriraj se</button>
</form>
