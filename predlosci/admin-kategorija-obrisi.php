<?php
$naslov = 'Brisanje kategorije';
$aktivno = 'administracija';
?>
<div class="obrazac-kartica">
    <?php if ($broj_vijesti > 0): ?>
        <p class="poruka poruka--greska" role="alert">
            Kategorija „<?= e($kategorija['naziv']) ?>” se ne može obrisati jer je koristi <?= e($broj_vijesti) ?>
            <?= $broj_vijesti === 1 ? 'vijest' : 'vijesti' ?>. Prvo premjesti te vijesti u drugu kategoriju.
        </p>
    <?php else: ?>
        <p>Želiš li obrisati kategoriju „<?= e($kategorija['naziv']) ?>”? Brisanje se ne može poništiti.</p>

        <form method="post" action="index.php?stranica=admin-kategorija-obrisi&amp;id=<?= e($kategorija['id']) ?>">
            <?= csrf_polje() ?>
            <button class="gumb gumb--opasno obrazac-kartica__gumb" type="submit">Obriši</button>
        </form>
    <?php endif; ?>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=admin-kategorije">Natrag na kategorije</a></p>
</div>
