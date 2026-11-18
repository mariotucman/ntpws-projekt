<?php
$naslov = $vijest['naslov'];
$opis = $vijest['autor'] . ' · ' . datum($vijest['objavljeno_u'], 'j. n. Y. \u H:i') . ' · '
    . ($vijest['grad_opcina'] !== null ? $vijest['grad_opcina'] . ', ' . $vijest['zupanija'] : 'Cijela Hrvatska');
$aktivno = 'vijesti';
?>
<img class="vijest__slika" src="<?= e(adresa_slike_vijesti($vijest['slika_id'])) ?>" alt="<?= $vijest['slika_id'] !== null ? e($vijest['naslov']) : '' ?>">

<article class="vijest">
    <p><span class="oznaka oznaka--kategorija"><?= e($vijest['kategorija']) ?></span></p>

    <p class="vijest__sazetak"><?= e($vijest['sazetak']) ?></p>

    <div class="vijest__sadrzaj">
        <?= $vijest['sadrzaj'] ?>
    </div>

    <p class="vijest__dno"><a href="index.php?stranica=vijesti">&larr; Sve vijesti</a></p>
</article>
