<?php
$naslov = 'Vijesti';
$opis = 'Novosti iz gradova i općina diljem Hrvatske';
$aktivno = 'vijesti';
?>
<form class="filtar" method="get" action="index.php">
    <input type="hidden" name="stranica" value="vijesti">
    <?php require __DIR__ . '/dijelovi/filtar-podrucja.php'; ?>
    <button class="gumb gumb--glavni" type="submit">Prikaži</button>
    <?php if ($odabrana_zupanija > 0): ?>
        <a href="index.php?stranica=vijesti">Poništi filtar</a>
    <?php endif; ?>
</form>

<?php if (count($vijesti) === 0): ?>
    <p><?= $odabrana_zupanija > 0 ? 'Za odabrano područje nema objavljenih vijesti.' : 'Trenutno nema objavljenih vijesti.' ?></p>
<?php else: ?>
    <?php foreach ($vijesti as $vijest): ?>
        <article class="vijest-kartica">
            <p class="vijest-kartica__kategorija"><?= e($vijest['kategorija']) ?></p>
            <h2 class="vijest-kartica__naslov"><?= e($vijest['naslov']) ?></h2>
            <p class="vijest-kartica__podaci">
                <?= $vijest['grad_opcina'] !== null ? e($vijest['grad_opcina'] . ', ' . $vijest['zupanija']) : 'Cijela Hrvatska' ?>
                &middot;
                <time datetime="<?= e(date('Y-m-d', strtotime($vijest['objavljeno_u']))) ?>"><?= e(date('j. n. Y.', strtotime($vijest['objavljeno_u']))) ?></time>
            </p>
            <p class="vijest-kartica__sazetak"><?= e($vijest['sazetak']) ?></p>
        </article>
    <?php endforeach; ?>
<?php endif; ?>
