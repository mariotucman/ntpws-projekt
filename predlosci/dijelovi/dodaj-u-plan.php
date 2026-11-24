<?php
// Kartica „Moj plan” na detalju lokacije i događanja: obrazac za dodavanje u jedan od budućih planova.
// Očekuje $vrsta_stavke ('lokacija' ili 'dogadanje'), $stavka_id, $zadano_vrijeme ('' ili 'H:i')
// i $planovi_za_dodavanje (rezultat dani_za_dodavanje(), ili null za neprijavljenog posjetitelja).
?>
<section class="bocna-kartica" id="moj-plan">
    <h2 class="bocna-kartica__naslov">Moj plan</h2>

    <?php if ($planovi_za_dodavanje === null): ?>
        <p><a href="index.php?stranica=prijava">Prijavi se</a> da dodaš u svoj plan putovanja.</p>
    <?php elseif (count($planovi_za_dodavanje) === 0): ?>
        <p class="bocna-kartica__napomena">
            <?= $vrsta_stavke === 'dogadanje' ? 'Događanje ne traje ni u jednom tvom nadolazećem planu.' : 'Nemaš nijedan nadolazeći plan.' ?>
        </p>
        <a class="gumb gumb--obrub" href="index.php?stranica=moj-plan">Napravi plan</a>
    <?php else: ?>
        <form class="dodaj-u-plan" method="post" action="index.php?stranica=plan-stavka-dodaj">
            <?= csrf_polje() ?>
            <input type="hidden" name="vrsta_stavke" value="<?= e($vrsta_stavke) ?>">
            <input type="hidden" name="stavka" value="<?= e($stavka_id) ?>">

            <div class="polje">
                <label class="polje__oznaka" for="plan_dan">Plan i dan</label>
                <select class="polje__unos" id="plan_dan" name="plan_dan" required>
                    <?php foreach ($planovi_za_dodavanje as $stavka_plana): ?>
                        <optgroup label="<?= e($stavka_plana['plan']['naziv']) ?>">
                            <?php foreach ($stavka_plana['dani'] as $dan): ?>
                                <option value="<?= e($stavka_plana['plan']['id'] . '|' . $dan) ?>"><?= e(naziv_dana($dan) . ', ' . datum($dan, 'j. n.')) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="polje">
                <label class="polje__oznaka" for="vrijeme">Vrijeme (nije obavezno)</label>
                <input class="polje__unos" type="time" id="vrijeme" name="vrijeme" value="<?= e($zadano_vrijeme) ?>">
            </div>

            <button class="gumb gumb--naglasak" type="submit">Dodaj u moj plan</button>
        </form>
    <?php endif; ?>
</section>
