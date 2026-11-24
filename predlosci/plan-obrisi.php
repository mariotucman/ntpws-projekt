<?php
$naslov = 'Brisanje plana';
$opis = $plan['naziv'];
$aktivno = 'moj-plan';
?>
<div class="obrazac-kartica">
    <p>
        Želiš li obrisati plan „<?= e($plan['naziv']) ?>”<?= $broj_stavki > 0 ? ' zajedno s ' . e($broj_stavki) . ' ' . ($broj_stavki === 1 ? 'stavkom' : 'stavki') : '' ?>?
        Brisanje se ne može poništiti.
    </p>

    <form method="post" action="index.php?stranica=plan-obrisi&amp;id=<?= e($plan['id']) ?>">
        <?= csrf_polje() ?>
        <button class="gumb gumb--opasno obrazac-kartica__gumb" type="submit">Obriši plan</button>
    </form>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=plan&amp;id=<?= e($plan['id']) ?>">Natrag na plan</a></p>
</div>
