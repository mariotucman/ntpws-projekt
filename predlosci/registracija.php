<?php
$naslov = 'Registracija';
$opis = 'Napravi račun i spremaj lokacije, piši recenzije i složi svoj plan puta.';
$aktivno = 'registracija';
?>
<form class="obrazac-kartica" method="post" action="index.php?stranica=registracija" novalidate>
    <h2 class="obrazac-kartica__naslov">Napravi račun</h2>

    <?php if ($uspjeh): ?>
        <p class="poruka poruka--uspjeh" role="status">Račun je napravljen. Sada se možeš prijaviti.</p>
    <?php endif; ?>

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

    <div class="polje">
        <label class="polje__oznaka" for="lozinka">Lozinka</label>
        <input class="polje__unos" type="password" id="lozinka" name="lozinka" autocomplete="new-password" required
            aria-describedby="lozinka-pomoc<?= isset($greske['lozinka']) ? ' lozinka-greska' : '' ?>"
            <?= isset($greske['lozinka']) ? 'aria-invalid="true"' : '' ?>>
        <p class="polje__pomoc" id="lozinka-pomoc">Najmanje 8 znakova.</p>
        <?php if (isset($greske['lozinka'])): ?>
            <p class="polje__greska" id="lozinka-greska"><?= e($greske['lozinka']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="lozinka_ponovo">Ponovi lozinku</label>
        <input class="polje__unos" type="password" id="lozinka_ponovo" name="lozinka_ponovo" autocomplete="new-password" required
            <?= isset($greske['lozinka_ponovo']) ? 'aria-invalid="true" aria-describedby="lozinka_ponovo-greska"' : '' ?>>
        <?php if (isset($greske['lozinka_ponovo'])): ?>
            <p class="polje__greska" id="lozinka_ponovo-greska"><?= e($greske['lozinka_ponovo']) ?></p>
        <?php endif; ?>
    </div>

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Registriraj se</button>
</form>
