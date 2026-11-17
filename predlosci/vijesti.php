<?php
$naslov = 'Vijesti';
$opis = 'Novosti iz gradova i općina diljem Hrvatske';
$aktivno = 'vijesti';
?>
<?php $filtrirano = $odabrana_zupanija > 0 || $odabrana_kategorija > 0 || $trazi !== ''; ?>
<form class="filtar" method="get" action="index.php">
    <input type="hidden" name="stranica" value="vijesti">

    <div class="polje">
        <label class="polje__oznaka" for="trazi">Pretraga</label>
        <input class="polje__unos" type="search" id="trazi" name="trazi" value="<?= e($trazi) ?>" maxlength="100">
    </div>

    <?php require __DIR__ . '/dijelovi/filtar-podrucja.php'; ?>

    <div class="polje">
        <label class="polje__oznaka" for="kategorija">Kategorija</label>
        <select class="polje__unos" id="kategorija" name="kategorija">
            <option value="">Sve kategorije</option>
            <?php foreach ($kategorije as $kategorija): ?>
                <option value="<?= e($kategorija['id']) ?>"<?= (int) $kategorija['id'] === $odabrana_kategorija ? ' selected' : '' ?>><?= e($kategorija['naziv']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filtar__radnje">
        <button class="gumb gumb--glavni" type="submit">Prikaži</button>
        <?php if ($filtrirano): ?>
            <a class="gumb gumb--obrub" href="index.php?stranica=vijesti">Poništi filtar</a>
        <?php endif; ?>
    </div>
</form>

<?php if (count($vijesti) === 0): ?>
    <p class="poruka poruka--prazno"><?= $filtrirano ? 'Nema objavljenih vijesti za odabrani filtar.' : 'Trenutno nema objavljenih vijesti.' ?></p>
<?php else: ?>
    <?php foreach ($vijesti as $vijest): ?>
        <article class="vijest-kartica">
            <p><span class="oznaka oznaka--kategorija"><?= e($vijest['kategorija']) ?></span></p>
            <h2 class="vijest-kartica__naslov"><a href="index.php?stranica=vijest&amp;id=<?= e($vijest['id']) ?>"><?= e($vijest['naslov']) ?></a></h2>
            <p class="vijest-kartica__podaci">
                <?= $vijest['grad_opcina'] !== null ? e($vijest['grad_opcina'] . ', ' . $vijest['zupanija']) : 'Cijela Hrvatska' ?>
                &middot;
                <time datetime="<?= e(date('Y-m-d', strtotime($vijest['objavljeno_u']))) ?>"><?= e(date('j. n. Y.', strtotime($vijest['objavljeno_u']))) ?></time>
            </p>
            <p class="vijest-kartica__sazetak"><?= e($vijest['sazetak']) ?></p>
        </article>
    <?php endforeach; ?>

    <?php require __DIR__ . '/dijelovi/stranicenje.php'; ?>
<?php endif; ?>
