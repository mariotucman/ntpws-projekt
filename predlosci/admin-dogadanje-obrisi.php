<?php
$naslov = 'Brisanje događanja';
$aktivno = 'administracija';
$admin_stavka = 'dogadanja';
?>
<div class="obrazac-kartica">
    <p>Želiš li obrisati događanje „<?= e($dogadanje['naziv']) ?>”?<?= $dogadanje['slika_id'] !== null ? ' Obrisat će se i njegova slika.' : '' ?> Brisanje se ne može poništiti.</p>

    <?php if ((int) $dogadanje['objavljeno'] === 1): ?>
        <p class="polje__pomoc">Događanje je objavljeno. Ako ga samo želiš maknuti s javnih stranica, umjesto brisanja ga sakrij.</p>
    <?php endif; ?>

    <form method="post" action="index.php?stranica=admin-dogadanje-obrisi&amp;id=<?= e($dogadanje['id']) ?>">
        <?= csrf_polje() ?>
        <button class="gumb gumb--opasno obrazac-kartica__gumb" type="submit">Obriši</button>
    </form>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=admin-dogadanja">Natrag na događanja</a></p>
</div>
