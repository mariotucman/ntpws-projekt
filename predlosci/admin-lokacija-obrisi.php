<?php
$naslov = 'Brisanje lokacije';
$aktivno = 'administracija';
$admin_stavka = 'lokacije';
?>
<div class="obrazac-kartica">
    <p>
        Želiš li obrisati lokaciju „<?= e($lokacija['naziv']) ?>”?
        <?php if ($broj_fotografija > 0): ?>
            Obrisat će se i sve njezine fotografije (<?= e($broj_fotografija) ?>).
        <?php endif; ?>
        Brisanje se ne može poništiti.
    </p>

    <?php if ((int) $lokacija['objavljeno'] === 1): ?>
        <p class="polje__pomoc">Lokacija je objavljena. Ako je samo želiš maknuti s javnih stranica, umjesto brisanja je sakrij.</p>
    <?php endif; ?>

    <form method="post" action="index.php?stranica=admin-lokacija-obrisi&amp;id=<?= e($lokacija['id']) ?>">
        <?= csrf_polje() ?>
        <button class="gumb gumb--opasno obrazac-kartica__gumb" type="submit">Obriši</button>
    </form>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=admin-lokacije">Natrag na lokacije</a></p>
</div>
