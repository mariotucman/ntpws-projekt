<?php
$naslov = $lokacija['naziv'];
$opis = $lokacija['grad_opcina'] . ', ' . $lokacija['zupanija'];
$aktivno = 'lokacije';
?>
<article class="lokacija">
    <p><span class="oznaka oznaka--vrsta"><?= e($lokacija['vrsta']) ?></span></p>

    <div class="lokacija__opis">
        <?= nl2br(e($lokacija['opis'])) ?>
    </div>

    <?php if ($lokacija['adresa'] !== null): ?>
        <p class="lokacija__adresa"><strong>Adresa:</strong> <?= e($lokacija['adresa'] . ', ' . $lokacija['grad_opcina']) ?></p>
    <?php endif; ?>

    <section class="radno-vrijeme">
        <h2 class="radno-vrijeme__naslov">Radno vrijeme</h2>

        <?php if ($radno_vrijeme === null): ?>
            <p>Lokacija je uvijek otvorena.</p>
        <?php else: ?>
            <table class="radno-vrijeme__tablica">
                <?php foreach ($radno_vrijeme['dani'] as [$dan, $vrijeme, $danas]): ?>
                    <tr class="radno-vrijeme__dan<?= $danas ? ' radno-vrijeme__dan--danas' : '' ?>">
                        <th scope="row"><?= e($dan) ?></th>
                        <td><?= e($vrijeme) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <?php if ($radno_vrijeme['napomena'] !== null): ?>
                <p class="radno-vrijeme__napomena"><?= e($radno_vrijeme['napomena']) ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </section>

    <p class="lokacija__dno"><a href="index.php?stranica=lokacije">&larr; Sve lokacije</a></p>
</article>
