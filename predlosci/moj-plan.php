<?php
$naslov = 'Moj plan';
$opis = 'Složi putovanje po danima od lokacija i događanja iz cijele Hrvatske.';
$aktivno = 'moj-plan';
$danas = date('Y-m-d');
?>
<div class="dva-stupca">
    <div class="dva-stupca__glavni">
        <section class="bocna-kartica">
            <h2 class="bocna-kartica__naslov">Moji planovi</h2>

            <?php if (count($planovi) === 0): ?>
                <p class="poruka poruka--prazno">Još nemaš nijedan plan. Napravi prvi u obrascu „Novi plan”.</p>
            <?php else: ?>
                <ul class="popis-planova">
                    <?php foreach ($planovi as $plan): ?>
                        <?php
                        $dana = broj_dana($plan['datum_od'], $plan['datum_do']);
                        $razdoblje = $plan['datum_od'] === $plan['datum_do']
                            ? datum($plan['datum_od'])
                            : datum($plan['datum_od'], 'j. n.') . ' – ' . datum($plan['datum_do']);
                        ?>
                        <li class="popis-planova__plan">
                            <p class="popis-planova__naziv"><?= e($plan['naziv']) ?></p>
                            <p class="popis-planova__podaci">
                                <?= e($razdoblje) ?> · <?= e($dana) ?> <?= $dana === 1 ? 'dan' : 'dana' ?> · <?= e($plan['broj_stavki']) ?> <?= (int) $plan['broj_stavki'] === 1 ? 'stavka' : 'stavki' ?>
                            </p>
                            <?php if ($plan['datum_do'] < $danas): ?>
                                <p><span class="oznaka">Prošao</span></p>
                            <?php elseif ($plan['datum_od'] <= $danas): ?>
                                <p><span class="oznaka oznaka--vrsta">U tijeku</span></p>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>

    <aside class="dva-stupca__bocni">
        <form class="bocna-kartica" method="post" action="index.php?stranica=moj-plan" novalidate>
            <?= csrf_polje() ?>
            <h2 class="bocna-kartica__naslov">Novi plan</h2>

            <div class="polje">
                <label class="polje__oznaka" for="naziv">Naziv</label>
                <input class="polje__unos<?= isset($greske['naziv']) ? ' polje__unos--greska' : '' ?>" type="text" id="naziv" name="naziv" value="<?= e($obrazac['naziv']) ?>" maxlength="<?= PLAN_NAJDULJI_NAZIV ?>" required>
                <p class="polje__pomoc">Npr. Vikend u Zagrebu.</p>
                <?php if (isset($greske['naziv'])): ?>
                    <p class="polje__greska"><?= e($greske['naziv']) ?></p>
                <?php endif; ?>
            </div>

            <div class="polje">
                <label class="polje__oznaka" for="datum_od">Prvi dan</label>
                <input class="polje__unos<?= isset($greske['datum_od']) ? ' polje__unos--greska' : '' ?>" type="date" id="datum_od" name="datum_od" value="<?= e($obrazac['datum_od']) ?>" min="<?= e($danas) ?>" required>
                <?php if (isset($greske['datum_od'])): ?>
                    <p class="polje__greska"><?= e($greske['datum_od']) ?></p>
                <?php endif; ?>
            </div>

            <div class="polje">
                <label class="polje__oznaka" for="datum_do">Zadnji dan</label>
                <input class="polje__unos<?= isset($greske['datum_do']) ? ' polje__unos--greska' : '' ?>" type="date" id="datum_do" name="datum_do" value="<?= e($obrazac['datum_do']) ?>" min="<?= e($danas) ?>" required>
                <p class="polje__pomoc">Plan može trajati najviše <?= PLAN_NAJVISE_DANA ?> dana.</p>
                <?php if (isset($greske['datum_do'])): ?>
                    <p class="polje__greska"><?= e($greske['datum_do']) ?></p>
                <?php endif; ?>
            </div>

            <button class="gumb gumb--glavni" type="submit">Napravi plan</button>
        </form>
    </aside>
</div>
