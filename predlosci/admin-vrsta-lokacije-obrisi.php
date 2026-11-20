<?php
$naslov = 'Brisanje vrste lokacije';
$aktivno = 'administracija';
$admin_stavka = 'vrste-lokacija';
?>
<div class="obrazac-kartica">
    <?php if ($broj_lokacija > 0): ?>
        <p class="poruka poruka--greska">
            Vrsta „<?= e($vrsta['naziv']) ?>” se ne može obrisati jer je koristi <?= e($broj_lokacija) ?> lokacija.
            Prvo tim lokacijama odaberi drugu vrstu.
        </p>
    <?php else: ?>
        <p>Želiš li obrisati vrstu „<?= e($vrsta['naziv']) ?>”? Brisanje se ne može poništiti.</p>

        <form method="post" action="index.php?stranica=admin-vrsta-lokacije-obrisi&amp;id=<?= e($vrsta['id']) ?>">
            <?= csrf_polje() ?>
            <button class="gumb gumb--opasno obrazac-kartica__gumb" type="submit">Obriši</button>
        </form>
    <?php endif; ?>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=admin-vrste-lokacija">Natrag na vrste lokacija</a></p>
</div>
