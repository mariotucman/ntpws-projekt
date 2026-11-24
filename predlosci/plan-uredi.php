<?php
$naslov = 'Uređivanje plana';
$opis = $plan['naziv'];
$aktivno = 'moj-plan';
?>
<form class="obrazac-kartica" method="post" action="index.php?stranica=plan-uredi&amp;id=<?= e($plan['id']) ?>" novalidate>
    <?= csrf_polje() ?>
    <h2 class="obrazac-kartica__naslov">Naziv i datumi</h2>

    <div class="polje">
        <label class="polje__oznaka" for="naziv">Naziv</label>
        <input class="polje__unos<?= isset($greske['naziv']) ? ' polje__unos--greska' : '' ?>" type="text" id="naziv" name="naziv" value="<?= e($obrazac['naziv']) ?>" maxlength="<?= PLAN_NAJDULJI_NAZIV ?>" required>
        <?php if (isset($greske['naziv'])): ?>
            <p class="polje__greska"><?= e($greske['naziv']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="datum_od">Prvi dan</label>
        <input class="polje__unos<?= isset($greske['datum_od']) ? ' polje__unos--greska' : '' ?>" type="date" id="datum_od" name="datum_od" value="<?= e($obrazac['datum_od']) ?>" required>
        <?php if (isset($greske['datum_od'])): ?>
            <p class="polje__greska"><?= e($greske['datum_od']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="datum_do">Zadnji dan</label>
        <input class="polje__unos<?= isset($greske['datum_do']) ? ' polje__unos--greska' : '' ?>" type="date" id="datum_do" name="datum_do" value="<?= e($obrazac['datum_do']) ?>" required>
        <p class="polje__pomoc">Plan može trajati najviše <?= PLAN_NAJVISE_DANA ?> dana. Stavke s dana koji ispadnu iz razdoblja brišu se.</p>
        <?php if (isset($greske['datum_do'])): ?>
            <p class="polje__greska"><?= e($greske['datum_do']) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($stavki_izvan > 0): ?>
        <div class="polje">
            <p class="poruka poruka--greska">
                Na danima izvan novog razdoblja <?= $stavki_izvan === 1 ? 'je 1 stavka' : 'su ' . e($stavki_izvan) . ' stavki' ?>.
                Spremanjem će se obrisati.
            </p>
            <label class="polje__kvacica">
                <input type="checkbox" name="potvrda" value="1">
                Da, obriši te stavke
            </label>
            <?php if (isset($greske['potvrda'])): ?>
                <p class="polje__greska"><?= e($greske['potvrda']) ?></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Spremi</button>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=plan&amp;id=<?= e($plan['id']) ?>">Natrag na plan</a></p>
</form>
