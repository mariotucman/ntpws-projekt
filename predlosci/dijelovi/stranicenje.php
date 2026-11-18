<?php
// Zajedničko straničenje popisa
// Ako postoji samo jedna stranica, ništa se ne prikazuje
if ($broj_stranica <= 1) {
    return;
}

$poveznica = function ($broj) use ($adresa) {
    return 'index.php?' . http_build_query($broj > 1 ? $adresa + ['str' => $broj] : $adresa);
};
?>
<nav class="stranicenje">
    <?php if ($trenutna_stranica > 1): ?>
        <a class="stranicenje__poveznica" href="<?= e($poveznica($trenutna_stranica - 1)) ?>" rel="prev">Prethodna</a>
    <?php endif; ?>

    <?php for ($broj = 1; $broj <= $broj_stranica; $broj++): ?>
        <?php if ($broj === $trenutna_stranica): ?>
            <span class="stranicenje__poveznica stranicenje__poveznica--trenutna"><?= $broj ?></span>
        <?php else: ?>
            <a class="stranicenje__poveznica" href="<?= e($poveznica($broj)) ?>"><?= $broj ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($trenutna_stranica < $broj_stranica): ?>
        <a class="stranicenje__poveznica" href="<?= e($poveznica($trenutna_stranica + 1)) ?>" rel="next">Sljedeća</a>
    <?php endif; ?>
</nav>
