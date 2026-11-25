<?php
// „Što te zanima?” u gornjem bloku početne: svaka vrsta vodi na popis lokacija te vrste
?>
<nav class="izbor">
    <?php foreach ($vrste as $vrsta): ?>
        <a class="izbor__stavka" href="index.php?stranica=lokacije&amp;vrsta=<?= e($vrsta['id']) ?>">
            <?= e($vrsta['naziv']) ?>
            <span class="izbor__broj"><?= e($vrsta['broj_lokacija']) ?></span>
        </a>
    <?php endforeach; ?>
    <a class="izbor__stavka" href="index.php?stranica=lokacije">Sve lokacije</a>
</nav>
