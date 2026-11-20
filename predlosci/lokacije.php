<?php
$naslov = 'Lokacije';
$opis = 'Mjesta koja vrijedi posjetiti diljem Hrvatske';
$aktivno = 'lokacije';
?>
<?php $filtrirano = $odabrana_zupanija > 0 || $odabrana_vrsta > 0 || $trazi !== ''; ?>
<form class="filtar" method="get" action="index.php">
    <input type="hidden" name="stranica" value="lokacije">

    <div class="polje">
        <label class="polje__oznaka" for="trazi">Pretraga</label>
        <input class="polje__unos" type="search" id="trazi" name="trazi" value="<?= e($trazi) ?>" maxlength="100">
    </div>

    <?php require __DIR__ . '/dijelovi/filtar-podrucja.php'; ?>

    <div class="polje">
        <label class="polje__oznaka" for="vrsta">Vrsta</label>
        <select class="polje__unos" id="vrsta" name="vrsta">
            <option value="">Sve vrste</option>
            <?php foreach ($vrste as $vrsta): ?>
                <option value="<?= e($vrsta['id']) ?>"<?= (int) $vrsta['id'] === $odabrana_vrsta ? ' selected' : '' ?>><?= e($vrsta['naziv']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filtar__radnje">
        <button class="gumb gumb--glavni" type="submit">Prikaži</button>
        <?php if ($filtrirano): ?>
            <a class="gumb gumb--obrub" href="index.php?stranica=lokacije">Poništi filtar</a>
        <?php endif; ?>
    </div>
</form>

<?php if (count($lokacije) === 0): ?>
    <p class="poruka poruka--prazno"><?= $filtrirano ? 'Nema lokacija za odabrani filtar.' : 'Trenutno nema objavljenih lokacija.' ?></p>
<?php else: ?>
    <?php foreach ($lokacije as $lokacija): ?>
        <article class="lokacija-kartica">
            <p><span class="oznaka oznaka--vrsta"><?= e($lokacija['vrsta']) ?></span></p>
            <h2 class="lokacija-kartica__naziv"><a href="index.php?stranica=lokacija&amp;id=<?= e($lokacija['id']) ?>"><?= e($lokacija['naziv']) ?></a></h2>
            <p class="lokacija-kartica__podrucje"><?= e($lokacija['grad_opcina'] . ', ' . $lokacija['zupanija']) ?></p>
            <p class="lokacija-kartica__opis"><?= e(mb_strimwidth($lokacija['opis'], 0, 160, '…')) ?></p>
        </article>
    <?php endforeach; ?>

    <?php require __DIR__ . '/dijelovi/stranicenje.php'; ?>
<?php endif; ?>
