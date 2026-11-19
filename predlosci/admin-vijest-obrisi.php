<?php
$naslov = 'Brisanje vijesti';
$aktivno = 'administracija';
$admin_stavka = 'vijesti';
?>
<div class="obrazac-kartica">
    <p>Želiš li obrisati vijest „<?= e($vijest['naslov']) ?>”?<?= $vijest['slika_id'] !== null ? ' Obrisat će se i njezina slika.' : '' ?> Brisanje se ne može poništiti.</p>

    <?php if ($vijest['status'] === 'objavljeno'): ?>
        <p class="polje__pomoc">Vijest je objavljena. Ako je samo želiš maknuti s javnih stranica, umjesto brisanja je povuci.</p>
    <?php endif; ?>

    <form method="post" action="index.php?stranica=admin-vijest-obrisi&amp;id=<?= e($vijest['id']) ?>">
        <?= csrf_polje() ?>
        <button class="gumb gumb--opasno obrazac-kartica__gumb" type="submit">Obriši</button>
    </form>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=admin-vijesti">Natrag na vijesti</a></p>
</div>
