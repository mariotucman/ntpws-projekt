<?php
$naslov = $dogadanje['naziv'];
$opis = $dogadanje['vrsta'] . ' · ' . $dogadanje['grad_opcina'] . ', ' . $dogadanje['zupanija'];
$aktivno = 'dogadanja';

// Početak, npr. „Subota, 28. 11. 2026. u 10:00”, a završetak istog dana samo kao vrijeme („do 22:00”)
$pocetak = naziv_dana($dogadanje['pocetak']) . ', ' . datum($dogadanje['pocetak'], 'j. n. Y. \u H:i');
$zavrsetak = null;
if ($dogadanje['zavrsetak'] !== null) {
    $isti_dan = datum($dogadanje['zavrsetak'], 'Y-m-d') === datum($dogadanje['pocetak'], 'Y-m-d');
    $zavrsetak = $isti_dan
        ? 'do ' . datum($dogadanje['zavrsetak'], 'H:i')
        : naziv_dana($dogadanje['zavrsetak']) . ', ' . datum($dogadanje['zavrsetak'], 'j. n. Y. \u H:i');
}
?>
<?php if ($slika !== null): ?>
    <img class="dogadanje-detalj__slika" src="index.php?stranica=slika&amp;id=<?= e($slika['id']) ?>" width="<?= e($slika['sirina']) ?>" height="<?= e($slika['visina']) ?>" alt="<?= e($dogadanje['naziv']) ?>">
<?php endif; ?>

<div class="dva-stupca">
    <div class="dva-stupca__glavni">
        <article class="dogadanje-detalj">
            <p><span class="oznaka oznaka--vrsta"><?= e($dogadanje['vrsta']) ?></span></p>

            <?php if ($zavrsilo): ?>
                <p class="poruka poruka--prazno">Događanje je završilo.</p>
            <?php endif; ?>

            <h2 class="dogadanje-detalj__naslov">O događanju</h2>

            <div class="dogadanje-detalj__opis">
                <?= nl2br(e($dogadanje['opis'])) ?>
            </div>

            <p class="dogadanje-detalj__dno"><a href="index.php?stranica=dogadanja">&larr; Sva događanja</a></p>
        </article>
    </div>

    <aside class="dva-stupca__bocni">
        <section class="bocna-kartica">
            <h2 class="bocna-kartica__naslov">Kada</h2>
            <p class="bocna-kartica__istaknuto"><?= e($pocetak) ?></p>
            <?php if ($zavrsetak !== null): ?>
                <p><?= $isti_dan ? '' : 'Traje do: ' ?><?= e($zavrsetak) ?></p>
            <?php endif; ?>
        </section>

        <section class="bocna-kartica">
            <h2 class="bocna-kartica__naslov">Gdje</h2>
            <p>
                <?php if ($dogadanje['lokacija'] !== null && (int) $dogadanje['lokacija_objavljena'] === 1): ?>
                    <a href="index.php?stranica=lokacija&amp;id=<?= e($dogadanje['lokacija_id']) ?>"><?= e($dogadanje['lokacija']) ?></a><br>
                <?php elseif ($dogadanje['lokacija'] !== null): ?>
                    <?= e($dogadanje['lokacija']) ?><br>
                <?php endif; ?>
                <?php if ($dogadanje['adresa'] !== null): ?>
                    <?= e($dogadanje['adresa']) ?><br>
                <?php endif; ?>
                <?= e($dogadanje['grad_opcina']) ?>
            </p>
            <?php if ((int) $dogadanje['na_otvorenom'] === 1): ?>
                <p class="bocna-kartica__napomena">Održava se na otvorenom.</p>
            <?php endif; ?>
        </section>

        <section class="bocna-kartica">
            <h2 class="bocna-kartica__naslov">Ulaz</h2>
            <p class="bocna-kartica__istaknuto"><?= e(ucfirst(cijena($dogadanje['cijena']))) ?></p>
        </section>
    </aside>
</div>
