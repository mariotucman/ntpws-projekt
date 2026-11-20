<?php
$naslov = $lokacija['naziv'];
$opis = $lokacija['grad_opcina'] . ', ' . $lokacija['zupanija'];
$aktivno = 'lokacije';
?>
<div class="galerija">
    <?php if (count($fotografije) === 0): ?>
        <img class="galerija__stavka galerija__slika" src="<?= e(adresa_slike_lokacije(null)) ?>" alt="">
    <?php endif; ?>
    <?php foreach ($fotografije as $broj => $fotografija): ?>
        <!-- Klik otvara fotografiju u punoj veličini, a uz JavaScript u povećanju preko stranice -->
        <a class="galerija__stavka" href="<?= e(adresa_slike_lokacije($fotografija['id'])) ?>">
            <img class="galerija__slika" src="<?= e(adresa_slike_lokacije($fotografija['id'])) ?>" width="<?= e($fotografija['sirina']) ?>" height="<?= e($fotografija['visina']) ?>"
                alt="<?= e($lokacija['naziv']) ?>, fotografija <?= $broj + 1 ?>"<?= $broj > 0 ? ' loading="lazy"' : '' ?>>
        </a>
    <?php endforeach; ?>
</div>

<?php if (count($fotografije) > 0): ?>
    <dialog class="povecanje">
        <div class="povecanje__vrh">
            <p class="povecanje__brojac"></p>
            <button class="povecanje__gumb povecanje__gumb--zatvori" type="button">Zatvori</button>
        </div>

        <img class="povecanje__slika" src="" alt="">

        <?php if (count($fotografije) > 1): ?>
            <div class="povecanje__dno">
                <button class="povecanje__gumb povecanje__gumb--prethodna" type="button">&larr; Prethodna</button>
                <button class="povecanje__gumb povecanje__gumb--sljedeca" type="button">Sljedeća &rarr;</button>
            </div>
        <?php endif; ?>
    </dialog>

    <script src="js/galerija.js" defer></script>
<?php endif; ?>

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
