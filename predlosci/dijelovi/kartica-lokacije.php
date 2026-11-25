<?php
// Velika kartica lokacije (popis lokacija i početna). Treba $lokacija iz lokacije_javne().
?>
<article class="lokacija-kartica">
    <img class="lokacija-kartica__slika" src="<?= e(adresa_slike_lokacije($lokacija['slika_id'])) ?>" alt="" loading="lazy">
    <div class="lokacija-kartica__tijelo">
        <h2 class="lokacija-kartica__naziv"><a href="index.php?stranica=lokacija&amp;id=<?= e($lokacija['id']) ?>"><?= e($lokacija['naziv']) ?></a></h2>
        <p class="lokacija-kartica__podrucje"><?= e($lokacija['grad_opcina'] . ', ' . $lokacija['zupanija']) ?></p>
        <p class="lokacija-kartica__opis"><?= e(mb_strimwidth($lokacija['opis'], 0, 160, '…')) ?></p>
    </div>
    <!-- Traka na dnu: lijevo vrsta, desno prosječna ocjena i broj recenzija -->
    <p class="lokacija-kartica__traka">
        <span class="lokacija-kartica__vrsta"><?= e($lokacija['vrsta']) ?></span>
        <?php if ((int) $lokacija['broj_recenzija'] > 0): ?>
            <span class="lokacija-kartica__ocjena">&#9733; <?= e(ocjena($lokacija['prosjek'])) ?> <small>(<?= e($lokacija['broj_recenzija']) ?>)</small></span>
        <?php else: ?>
            <span class="lokacija-kartica__bez-ocjene">Bez recenzija</span>
        <?php endif; ?>
    </p>
</article>
