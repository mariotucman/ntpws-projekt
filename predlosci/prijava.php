<?php
$naslov = 'Prijava';
$opis = 'Prijavi se i nastavi slagati svoj plan puta.';
$aktivno = 'prijava';
?>
<form class="obrazac-kartica" method="post" action="index.php?stranica=prijava" novalidate>
    <?= csrf_polje() ?>
    <h2 class="obrazac-kartica__naslov">Dobro došao natrag</h2>

    <?php if ($greska !== ''): ?>
        <p class="poruka poruka--greska" role="alert"><?= e($greska) ?></p>
    <?php endif; ?>

    <div class="polje">
        <label class="polje__oznaka" for="email">Email</label>
        <input class="polje__unos" type="email" id="email" name="email" value="<?= e($email) ?>" autocomplete="email" required>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="lozinka">Lozinka</label>
        <input class="polje__unos" type="password" id="lozinka" name="lozinka" autocomplete="current-password" required>
    </div>

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Prijavi se</button>

    <p class="obrazac-kartica__dno">Nemaš račun? <a href="index.php?stranica=registracija">Registriraj se</a></p>
</form>
