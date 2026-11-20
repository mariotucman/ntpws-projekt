<?php
$naslov = $lokacija['naziv'];
$opis = $lokacija['grad_opcina'] . ', ' . $lokacija['zupanija'];
$aktivno = 'lokacije';
?>
<div class="dva-stupca">
    <article class="lokacija dva-stupca__glavni">
        <p><span class="oznaka oznaka--vrsta"><?= e($lokacija['vrsta']) ?></span></p>

        <h2 class="lokacija__naslov">O lokaciji</h2>

        <div class="lokacija__opis">
            <?= nl2br(e($lokacija['opis'])) ?>
        </div>

        <p class="lokacija__dno"><a href="index.php?stranica=lokacije">&larr; Sve lokacije</a></p>
    </article>

    <aside class="dva-stupca__bocni">
        <section class="bocna-kartica">
            <h2 class="bocna-kartica__naslov">Radno vrijeme</h2>

            <?php if ($radno_vrijeme === null): ?>
                <p class="bocna-kartica__istaknuto">Uvijek otvoreno</p>
            <?php else: ?>
                <dl class="radno-vrijeme">
                    <?php foreach ($radno_vrijeme['dani'] as [$dan, $vrijeme, $danas]): ?>
                        <div class="radno-vrijeme__dan<?= $danas ? ' radno-vrijeme__dan--danas' : '' ?>">
                            <dt><?= e($dan) ?></dt>
                            <dd><?= e($vrijeme) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>

                <?php if ($radno_vrijeme['napomena'] !== null): ?>
                    <p class="bocna-kartica__napomena"><?= e($radno_vrijeme['napomena']) ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <?php if ($lokacija['adresa'] !== null): ?>
            <section class="bocna-kartica">
                <h2 class="bocna-kartica__naslov">Adresa</h2>
                <p><?= e($lokacija['adresa']) ?><br><?= e($lokacija['grad_opcina']) ?></p>
            </section>
        <?php endif; ?>
    </aside>
</div>
