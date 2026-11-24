<?php
$naslov = 'Dodaj u plan';
$opis = $plan['naziv'] . ' · ' . naziv_dana($dan) . ', ' . datum($dan);
$aktivno = 'moj-plan';
$oznaka_svi_gradovi = 'Svi gradovi i općine';
$akcija = 'index.php?' . http_build_query($trenutna_stranica > 1 ? $adresa + ['str' => $trenutna_stranica] : $adresa);
?>
<p><a href="index.php?stranica=plan&amp;id=<?= e($plan['id']) ?>#dan-<?= e($dan) ?>">&larr; Natrag na plan</a></p>

<form class="filtar" method="get" action="index.php">
    <input type="hidden" name="stranica" value="plan-dodaj">
    <input type="hidden" name="id" value="<?= e($plan['id']) ?>">
    <input type="hidden" name="dan" value="<?= e($dan) ?>">

    <div class="polje">
        <label class="polje__oznaka" for="vrsta_stavke">Što dodaješ</label>
        <select class="polje__unos" id="vrsta_stavke" name="vrsta_stavke">
            <option value="lokacije"<?= $vrsta_stavke === 'lokacije' ? ' selected' : '' ?>>Lokacije</option>
            <option value="dogadanja"<?= $vrsta_stavke === 'dogadanja' ? ' selected' : '' ?>>Događanja tog dana</option>
        </select>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="trazi">Naziv</label>
        <input class="polje__unos" type="search" id="trazi" name="trazi" value="<?= e($trazi) ?>" maxlength="100">
    </div>

    <?php require __DIR__ . '/dijelovi/filtar-podrucja.php'; ?>

    <div class="filtar__radnje">
        <button class="gumb gumb--glavni" type="submit">Traži</button>
    </div>
</form>

<?php if (count($rezultati) === 0): ?>
    <p class="poruka poruka--prazno">
        <?= $vrsta_stavke === 'lokacije' ? 'Nema lokacija' : 'Nema događanja koja traju taj dan' ?> za odabrane filtre.
        Promijeni naziv ili odaberi drugi grad/općinu.
    </p>
<?php else: ?>
    <p class="plan-dodaj__broj">
        <?= $vrsta_stavke === 'lokacije' ? 'Lokacija' : 'Događanja' ?>: <?= e($ukupno) ?>
    </p>

    <ul class="plan-dodaj">
        <?php foreach ($rezultati as $rezultat): ?>
            <?php
            if ($vrsta_stavke === 'lokacije') {
                $tip = 'lokacija';
                $vec_u_planu = in_array((int) $rezultat['id'], $u_planu['lokacije'], true);
                $poveznica = 'index.php?stranica=lokacija&id=' . $rezultat['id'];
                $podaci = $rezultat['vrsta'] . ' · ' . $rezultat['grad_opcina'];
                $zadano_vrijeme = '';
            } else {
                $tip = 'dogadanje';
                $vec_u_planu = in_array((int) $rezultat['id'], $u_planu['dogadanja'], true);
                $poveznica = 'index.php?stranica=dogadanje&id=' . $rezultat['id'];
                // Događanje koje počinje taj dan nudi svoje vrijeme početka
                $pocinje_taj_dan = substr($rezultat['pocetak'], 0, 10) === $dan;
                $zadano_vrijeme = $pocinje_taj_dan ? datum($rezultat['pocetak'], 'H:i') : '';
                $podaci = $rezultat['vrsta'] . ' · ' . $rezultat['grad_opcina']
                    . ($pocinje_taj_dan ? ' · počinje u ' . $zadano_vrijeme : ' · traje od ' . datum($rezultat['pocetak'], 'j. n.'));
            }
            ?>
            <li class="plan-dodaj__rezultat">
                <img class="plan-dodaj__slika" src="<?= e(adresa_slike_lokacije($rezultat['slika_id'])) ?>" width="96" height="72" alt="" loading="lazy">
                <div class="plan-dodaj__opis">
                    <p class="plan-dodaj__naziv"><a href="<?= e($poveznica) ?>"><?= e($rezultat['naziv']) ?></a></p>
                    <p class="plan-dodaj__podaci"><?= e($podaci) ?></p>
                </div>

                <?php if ($vec_u_planu): ?>
                    <span class="oznaka oznaka--status">Već u planu</span>
                <?php else: ?>
                    <form class="plan-dodaj__obrazac" method="post" action="<?= e($akcija) ?>">
                        <?= csrf_polje() ?>
                        <input type="hidden" name="vrsta_stavke" value="<?= e($tip) ?>">
                        <input type="hidden" name="stavka" value="<?= e($rezultat['id']) ?>">
                        <label class="polje__oznaka" for="vrijeme-<?= e($tip . '-' . $rezultat['id']) ?>">Vrijeme (nije obavezno)</label>
                        <input class="polje__unos" type="time" id="vrijeme-<?= e($tip . '-' . $rezultat['id']) ?>" name="vrijeme" value="<?= e($zadano_vrijeme) ?>">
                        <button class="gumb gumb--glavni" type="submit">Dodaj</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php require __DIR__ . '/dijelovi/stranicenje.php'; ?>
<?php endif; ?>
