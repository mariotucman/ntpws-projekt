<?php
$naslov = 'Brisanje vrste događanja';
$aktivno = 'administracija';
$admin_stavka = 'vrste-dogadanja';
?>
<div class="obrazac-kartica">
    <?php if ($broj_dogadanja > 0): ?>
        <p class="poruka poruka--greska">
            Vrsta „<?= e($vrsta['naziv']) ?>” se ne može obrisati jer je koristi <?= e($broj_dogadanja) ?> događanja.
            Prvo tim događanjima odaberi drugu vrstu.
        </p>
    <?php else: ?>
        <p>Želiš li obrisati vrstu „<?= e($vrsta['naziv']) ?>”? Brisanje se ne može poništiti.</p>

        <form method="post" action="index.php?stranica=admin-vrsta-dogadanja-obrisi&amp;id=<?= e($vrsta['id']) ?>">
            <?= csrf_polje() ?>
            <button class="gumb gumb--opasno obrazac-kartica__gumb" type="submit">Obriši</button>
        </form>
    <?php endif; ?>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=admin-vrste-dogadanja">Natrag na vrste događanja</a></p>
</div>
