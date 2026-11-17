<?php
$naslov = $vijest['naslov'];
$opis = $vijest['autor'] . ' · ' . date('j. n. Y. \u H:i', strtotime($vijest['objavljeno_u'])) . ' · '
    . ($vijest['grad_opcina'] !== null ? $vijest['grad_opcina'] . ', ' . $vijest['zupanija'] : 'Cijela Hrvatska');
$aktivno = 'vijesti';
?>
<article class="vijest">
    <p><span class="oznaka oznaka--kategorija"><?= e($vijest['kategorija']) ?></span></p>

    <p class="vijest__sazetak"><?= e($vijest['sazetak']) ?></p>

    <div class="vijest__sadrzaj">
        <?= $vijest['sadrzaj'] ?>
    </div>

    <p class="vijest__dno"><a href="index.php?stranica=vijesti">&larr; Sve vijesti</a></p>
</article>
