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
            <h2 class="dan-plana__naslov"><?= e(naziv_dana($dan) . ', ' . datum($dan, 'j. n.')) ?></h2>

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
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <a class="gumb gumb--obrub dan-plana__dodaj" href="index.php?stranica=plan-dodaj&amp;id=<?= e($plan['id']) ?>&amp;dan=<?= e($dan) ?>">Dodaj lokaciju ili događanje</a>
        </section>
    <?php endforeach; ?>
</div>
