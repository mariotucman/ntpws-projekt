<?php
$naslov = 'Što te zanima?';
$natpis = 'Turistički vodič po Hrvatskoj';
$opis = 'Lokacije, događanja, vijesti i vremenska prognoza za cijelu Hrvatsku na jednom mjestu. Odaberi što te zanima ili složi vlastiti plan putovanja.';
$aktivno = 'pocetna';
?>
<?php if (count($lokacije) > 0): ?>
    <section class="odjeljak">
        <div class="odjeljak__vrh">
            <h2 class="odjeljak__naslov">Istaknute lokacije</h2>
            <a class="odjeljak__poveznica" href="index.php?stranica=lokacije">Prikaži sve</a>
        </div>
        <div class="mreza-lokacija">
            <?php foreach ($lokacije as $lokacija): ?>
                <?php require __DIR__ . '/dijelovi/kartica-lokacije.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<div class="dva-stupca">
    <section class="odjeljak dva-stupca__glavni">
        <div class="odjeljak__vrh">
            <h2 class="odjeljak__naslov">Istaknuta događanja</h2>
            <a class="odjeljak__poveznica" href="index.php?stranica=dogadanja">Prikaži sve</a>
        </div>
        <?php if (count($dogadanja) === 0): ?>
            <p class="poruka poruka--prazno">Trenutno nema istaknutih događanja.</p>
        <?php else: ?>
            <div class="popis-dogadanja">
                <?php foreach ($dogadanja as $broj => $dogadanje): ?>
                    <?php require __DIR__ . '/dijelovi/kartica-dogadanja.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="odjeljak dva-stupca__bocni">
        <div class="odjeljak__vrh">
            <h2 class="odjeljak__naslov">Najnovije vijesti</h2>
            <a class="odjeljak__poveznica" href="index.php?stranica=vijesti">Prikaži sve</a>
        </div>
        <?php if (count($vijesti) === 0): ?>
            <p class="poruka poruka--prazno">Trenutno nema objavljenih vijesti.</p>
        <?php else: ?>
            <?php foreach ($vijesti as $vijest): ?>
                <article class="vijest-redak">
                    <img class="vijest-redak__slika" src="<?= e(adresa_slike_vijesti($vijest['slika_id'])) ?>" alt="" loading="lazy">
                    <div class="vijest-redak__tijelo">
                        <p class="vijest-redak__podaci">
                            <span class="oznaka oznaka--kategorija"><?= e($vijest['kategorija']) ?></span>
                            <span>
                                <time datetime="<?= e(datum($vijest['objavljeno_u'], 'Y-m-d')) ?>"><?= e(datum($vijest['objavljeno_u'], 'j. n.')) ?></time>
                                &middot; <?= e($vijest['grad_opcina'] ?? 'Cijela Hrvatska') ?>
                            </span>
                        </p>
                        <h3 class="vijest-redak__naslov"><a href="index.php?stranica=vijest&amp;id=<?= e($vijest['id']) ?>"><?= e($vijest['naslov']) ?></a></h3>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>

<section class="poziv">
    <div>
        <h2 class="poziv__naslov">Složi svoj plan putovanja</h2>
        <p class="poziv__opis">Dodaj lokacije i događanja po danima i uz svaki dan vidi prognozu.</p>
    </div>
    <a class="gumb gumb--tamni" href="index.php?stranica=moj-plan">Moj plan</a>
</section>
