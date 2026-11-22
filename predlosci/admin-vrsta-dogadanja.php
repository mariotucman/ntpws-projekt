<?php
$naslov = $vrsta === null ? 'Nova vrsta događanja' : 'Uredi vrstu događanja';
$aktivno = 'administracija';
$admin_stavka = 'vrste-dogadanja';
?>
<form class="obrazac-kartica" method="post" action="index.php?stranica=admin-vrsta-dogadanja<?= $vrsta !== null ? '&amp;id=' . e($vrsta['id']) : '' ?>" novalidate>
    <?= csrf_polje() ?>

    <div class="polje">
        <label class="polje__oznaka" for="naziv">Naziv</label>
        <input class="polje__unos<?= isset($greske['naziv']) ? ' polje__unos--greska' : '' ?>" type="text" id="naziv" name="naziv" value="<?= e($naziv) ?>" maxlength="60" required>
        <?php if (isset($greske['naziv'])): ?>
            <p class="polje__greska" id="naziv-greska"><?= e($greske['naziv']) ?></p>
        <?php endif; ?>
    </div>

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Spremi</button>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=admin-vrste-dogadanja">Natrag na vrste događanja</a></p>
</form>
