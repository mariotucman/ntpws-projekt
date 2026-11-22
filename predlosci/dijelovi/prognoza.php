<?php
// Prognoza po danima (kartice s ikonama), za detalj događanja i lokacije.
// Očekuje $prognoza (rezultat prognoza_razdoblja() ili null) i, za događanja, $prognoza_kasnije.
?>
<section class="odjeljak">
    <div class="odjeljak__vrh">
        <h2 class="odjeljak__naslov">Prognoza</h2>
        <?php if ($prognoza !== null && count($prognoza['dani']) > 0): ?>
            <?php
            // Vrijeme dohvaćanja uvijek se vidi; datum samo ako podaci nisu od danas
            $dohvaceno_danas = datum($prognoza['dohvaceno'], 'Y-m-d') === date('Y-m-d');
            $azurirano = datum($prognoza['dohvaceno'], $dohvaceno_danas ? '\u H:i' : 'j. n. \u H:i');
            ?>
            <p class="odjeljak__napomena"><a href="https://open-meteo.com">Open-Meteo</a> · ažurirano <?= e($azurirano) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($prognoza_kasnije ?? false): ?>
        <p class="poruka poruka--prazno">Prognoza će biti dostupna bliže datumu događanja.</p>
    <?php elseif ($prognoza === null || count($prognoza['dani']) === 0): ?>
        <p class="poruka poruka--prazno">Prognoza trenutno nije dostupna.</p>
    <?php else: ?>
        <?php if ($prognoza['zastarjelo']): ?>
            <p class="poruka poruka--prazno">Prognoza se trenutno ne može osvježiti, prikazani su zadnji podaci.</p>
        <?php endif; ?>
        <ul class="prognoza">
            <?php foreach ($prognoza['dani'] as $dan): ?>
                <?php
                if ($dan['datum'] === date('Y-m-d')) {
                    $naziv_dana = 'Danas';
                } elseif ($dan['datum'] === date('Y-m-d', strtotime('+1 day'))) {
                    $naziv_dana = 'Sutra';
                } else {
                    $naziv_dana = naziv_dana($dan['datum']);
                }

                // Oborine i vjetar za daleke dane znaju nedostajati pa se ispisuje samo ono što postoji
                $detalji = [];
                if ($dan['oborine'] !== null) {
                    $detalji[] = 'Oborine ' . $dan['oborine'] . ' %';
                }
                if ($dan['vjetar'] !== null) {
                    $detalji[] = 'Vjetar ' . (int) round($dan['vjetar']) . ' km/h';
                }
                ?>
                <li class="prognoza-kartica">
                    <div class="prognoza-kartica__vrh">
                        <div>
                            <p class="prognoza-kartica__dan"><?= e($naziv_dana) ?></p>
                            <p class="prognoza-kartica__datum"><?= e(datum($dan['datum'], 'j. n.')) ?></p>
                        </div>
                        <span class="prognoza-kartica__ikona prognoza-kartica__ikona--<?= e(ikona_vremena($dan['kod'])) ?>"></span>
                    </div>
                    <p class="prognoza-kartica__temperatura">
                        <?= e((int) round($dan['najvisa'])) ?>°<span class="prognoza-kartica__najniza"> / <?= e((int) round($dan['najniza'])) ?>°</span>
                    </p>
                    <p class="prognoza-kartica__opis"><?= e(opis_vremena($dan['kod'])) ?></p>
                    <?php if (count($detalji) > 0): ?>
                        <div class="prognoza-kartica__detalji">
                            <?php foreach ($detalji as $detalj): ?>
                                <p><?= e($detalj) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>
