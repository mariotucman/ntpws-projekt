<?php
$naslov = $plan['naziv'];
$dana = count($dani);
$opis = ($plan['datum_od'] === $plan['datum_do']
        ? datum($plan['datum_od'])
        : datum($plan['datum_od'], 'j. n.') . ' – ' . datum($plan['datum_do']))
    . ' · ' . $dana . ' ' . ($dana === 1 ? 'dan' : 'dana');
$aktivno = 'moj-plan';
?>
<div class="plan__akcije">
    <a class="gumb gumb--obrub" href="index.php?stranica=plan-uredi&amp;id=<?= e($plan['id']) ?>">Uredi plan</a>
    <a class="gumb gumb--obrub" href="index.php?stranica=plan-obrisi&amp;id=<?= e($plan['id']) ?>">Obriši plan</a>
    <a href="index.php?stranica=moj-plan">&larr; Moji planovi</a>
</div>

<div class="plan-dani">
    <?php foreach ($dani as $dan => $stavke): ?>
        <section class="dan-plana" id="dan-<?= e($dan) ?>">
            <div class="dan-plana__vrh">
                <h2 class="dan-plana__naslov"><?= e(naziv_dana($dan) . ', ' . datum($dan, 'j. n.')) ?></h2>
                <?php if (isset($prognoze[$dan])): ?>
                    <?php $prognoza_dana = $prognoze[$dan]['dan']; ?>
                    <p class="dan-plana__prognoza" title="<?= e(opis_vremena($prognoza_dana['kod']) . ', ' . $prognoze[$dan]['grad_opcina']) ?>">
                        <span class="ikona-vremena ikona-vremena--<?= e(ikona_vremena($prognoza_dana['kod'])) ?>"></span>
                        <?= e((int) round($prognoza_dana['najvisa'])) ?>° / <?= e((int) round($prognoza_dana['najniza'])) ?>°
                    </p>
                <?php endif; ?>
            </div>

            <?php if (count($stavke) === 0): ?>
                <p class="dan-plana__prazno">Još nema ništa za ovaj dan.</p>
            <?php else: ?>
                <ul class="dan-plana__stavke">
                    <?php foreach ($stavke as $stavka): ?>
                        <?php
                        $adresa = $stavka['lokacija_id'] !== null
                            ? 'index.php?stranica=lokacija&id=' . $stavka['lokacija_id']
                            : 'index.php?stranica=dogadanje&id=' . $stavka['dogadanje_id'];
                        $podaci = [$stavka['vrsta']];
                        if ($stavka['vrijeme'] !== null) {
                            $podaci[] = substr($stavka['vrijeme'], 0, 5);
                        }
                        $podaci[] = $stavka['grad_opcina'];
                        ?>
                        <li class="plan-stavka">
                            <img class="plan-stavka__slika" src="<?= e(adresa_slike_lokacije($stavka['slika_id'])) ?>" width="56" height="48" alt="" loading="lazy">
                            <div>
                                <p class="plan-stavka__naziv"><a href="<?= e($adresa) ?>"><?= e($stavka['naziv']) ?></a></p>
                                <p class="plan-stavka__podaci"><?= e(implode(' · ', $podaci)) ?></p>
                            </div>
                            <div class="plan-stavka__akcije">
                                <a class="plan-stavka__gumb" href="index.php?stranica=plan-stavka&amp;id=<?= e($stavka['id']) ?>">Promijeni</a>
                                <form method="post" action="index.php?stranica=plan-stavka-ukloni&amp;id=<?= e($stavka['id']) ?>">
                                    <?= csrf_polje() ?>
                                    <button class="plan-stavka__gumb" type="submit">Ukloni</button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <a class="gumb gumb--obrub dan-plana__dodaj" href="index.php?stranica=plan-dodaj&amp;id=<?= e($plan['id']) ?>&amp;dan=<?= e($dan) ?>">Dodaj lokaciju ili događanje</a>
        </section>
    <?php endforeach; ?>
</div>

<?php if ($prognoza_dohvaceno !== null): ?>
    <?php
    // Vrijeme dohvaćanja uvijek se vidi; datum samo ako podaci nisu od danas
    $dohvaceno_danas = datum($prognoza_dohvaceno, 'Y-m-d') === date('Y-m-d');
    $azurirano = datum($prognoza_dohvaceno, $dohvaceno_danas ? '\u H:i' : 'j. n. \u H:i');
    ?>
    <p class="plan__napomena">
        Prognoza je za grad/općinu prve stavke dana:
        <a href="https://open-meteo.com">Open-Meteo</a> · ažurirano <?= e($azurirano) ?><?= $prognoza_zastarjela ? ' (trenutno se ne može osvježiti)' : '' ?>
    </p>
<?php endif; ?>
