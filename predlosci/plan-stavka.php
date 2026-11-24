<?php
$naslov = 'Promjena stavke';
$opis = $stavka['naziv'] . ' · ' . $stavka['plan_naziv'];
$aktivno = 'moj-plan';
?>
<form class="obrazac-kartica" method="post" action="index.php?stranica=plan-stavka&amp;id=<?= e($stavka['id']) ?>" novalidate>
    <?= csrf_polje() ?>
    <h2 class="obrazac-kartica__naslov">Dan i vrijeme</h2>

    <div class="polje">
        <label class="polje__oznaka" for="dan">Dan</label>
        <select class="polje__unos<?= isset($greske['dan']) ? ' polje__unos--greska' : '' ?>" id="dan" name="dan">
            <?php foreach ($dani as $dan): ?>
                <option value="<?= e($dan) ?>"<?= $dan === $obrazac['dan'] ? ' selected' : '' ?>><?= e(naziv_dana($dan) . ', ' . datum($dan, 'j. n.')) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if ($stavka['dogadanje_id'] !== null): ?>
            <p class="polje__pomoc">Ponuđeni su samo dani plana u kojima događanje traje.</p>
        <?php endif; ?>
        <?php if (isset($greske['dan'])): ?>
            <p class="polje__greska"><?= e($greske['dan']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="vrijeme">Vrijeme (nije obavezno)</label>
        <input class="polje__unos<?= isset($greske['vrijeme']) ? ' polje__unos--greska' : '' ?>" type="time" id="vrijeme" name="vrijeme" value="<?= e($obrazac['vrijeme']) ?>">
        <p class="polje__pomoc">Stavke dana slažu se po vremenu, a one bez vremena idu na kraj.</p>
        <?php if (isset($greske['vrijeme'])): ?>
            <p class="polje__greska"><?= e($greske['vrijeme']) ?></p>
        <?php endif; ?>
    </div>

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Spremi</button>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=plan&amp;id=<?= e($stavka['plan_id']) ?>#dan-<?= e($stavka['dan']) ?>">Natrag na plan</a></p>
</form>
