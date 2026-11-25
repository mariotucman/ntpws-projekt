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
    <div class="mreza-lokacija">
        <?php foreach ($lokacije as $lokacija): ?>
            <?php require __DIR__ . '/dijelovi/kartica-lokacije.php'; ?>
        <?php endforeach; ?>
    </div>

    <?php require __DIR__ . '/dijelovi/stranicenje.php'; ?>
<?php endif; ?>
