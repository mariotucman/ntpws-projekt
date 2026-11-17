<?php
$naslov = $vijest['naslov'];
$opis = $vijest['sazetak'];
$aktivno = 'vijesti';
?>
<article class="vijest">
    <p class="vijest__podaci">
        <?= e($vijest['kategorija']) ?>
        &middot;
        <?= $vijest['grad_opcina'] !== null ? e($vijest['grad_opcina'] . ', ' . $vijest['zupanija']) : 'Cijela Hrvatska' ?>
        &middot;
        <time datetime="<?= e(date('Y-m-d\TH:i', strtotime($vijest['objavljeno_u']))) ?>"><?= e(date('j. n. Y. \u H:i', strtotime($vijest['objavljeno_u']))) ?></time>
        &middot;
        <?= e($vijest['autor']) ?>
    </p>

    <div class="vijest__sadrzaj">
        <?= $vijest['sadrzaj'] ?>
    </div>

    <p><a href="index.php?stranica=vijesti">&larr; Sve vijesti</a></p>
</article>
