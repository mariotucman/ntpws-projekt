<?php
$naslov = $kategorija === null ? 'Nova kategorija' : 'Uredi kategoriju';
$aktivno = 'administracija';
?>
<form class="obrazac-kartica" method="post" action="index.php?stranica=admin-kategorija<?= $kategorija !== null ? '&amp;id=' . e($kategorija['id']) : '' ?>" novalidate>
    <?= csrf_polje() ?>

    <div class="polje">
        <label class="polje__oznaka" for="naziv">Naziv</label>
        <input class="polje__unos" type="text" id="naziv" name="naziv" value="<?= e($naziv) ?>" maxlength="60" required
            <?= isset($greske['naziv']) ? 'aria-invalid="true" aria-describedby="naziv-greska"' : '' ?>>
        <?php if (isset($greske['naziv'])): ?>
            <p class="polje__greska" id="naziv-greska"><?= e($greske['naziv']) ?></p>
        <?php endif; ?>
    </div>

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Spremi</button>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=admin-kategorije">Natrag na kategorije</a></p>
</form>
