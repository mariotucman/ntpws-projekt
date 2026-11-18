<?php
// Stranice administracije imaju samo zaglavlje, a naslov stranice je u sadržaju
$aktivno ??= '';
$admin = $aktivno === 'administracija';
$prijavljeni = trenutni_korisnik();

// Poveznica na stranicu na kojoj se korisnik nalazi dobiva aria-current
$trenutna = function ($stranica) use ($aktivno) {
    return $aktivno === $stranica ? ' aria-current="page"' : '';
};
?>
<!doctype html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($naslov ?? 'Mjestopis') ?> | Mjestopis</title>
    <link rel="icon" href="data:,">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="css/stil.css">
</head>
<body>
<div class="stranica">
    <header class="vrh<?= isset($natpis) ? ' vrh--pocetna' : '' ?><?= $admin ? ' vrh--admin' : '' ?>">
        <div class="zaglavlje">
            <a class="logo" href="index.php">Mjestopis</a>

            <nav class="izbornik" aria-label="Glavni izbornik">
                <a class="izbornik__stavka" href="index.php"<?= $trenutna('pocetna') ?>>Početna</a>
                <a class="izbornik__stavka" href="index.php?stranica=vijesti"<?= $trenutna('vijesti') ?>>Vijesti</a>
                <?php if (ima_ulogu(['administrator', 'urednik'])): ?>
                    <a class="izbornik__stavka" href="index.php?stranica=admin-vijesti"<?= $trenutna('administracija') ?>>Administracija</a>
                <?php endif; ?>
            </nav>

            <div class="racun">
                <?php if ($prijavljeni !== null): ?>
                    <a class="racun__korisnik" href="index.php?stranica=profil">
                        <span class="racun__inicijali" aria-hidden="true"><?= e(inicijali($prijavljeni['ime'])) ?></span>
                        <?= e($prijavljeni['ime']) ?>
                    </a>
                    <form method="post" action="index.php?stranica=odjava">
                        <?= csrf_polje() ?>
                        <button class="racun__gumb" type="submit">Odjava</button>
                    </form>
                <?php else: ?>
                    <a class="racun__gumb" href="index.php?stranica=prijava"<?= $trenutna('prijava') ?>>Prijava</a>
                    <a class="racun__gumb racun__gumb--naglasak" href="index.php?stranica=registracija"<?= $trenutna('registracija') ?>>Registracija</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$admin): ?>
        <div>
            <?php if (isset($natpis)): ?>
                <p class="vrh__natpis"><?= e($natpis) ?></p>
            <?php endif; ?>
            <h1 class="vrh__naslov"><?= e($naslov ?? 'Mjestopis') ?></h1>
            <?php if (isset($opis)): ?>
                <p class="vrh__opis"><?= e($opis) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </header>

    <main class="sadrzaj">
        <?php if ($admin): ?>
            <nav class="admin-izbornik" aria-label="Administracija">
                <a class="admin-izbornik__stavka" href="index.php?stranica=admin-vijesti"<?= ($admin_stavka ?? '') === 'vijesti' ? ' aria-current="page"' : '' ?>>Vijesti</a>
                <?php if (ima_ulogu(['administrator'])): ?>
                    <a class="admin-izbornik__stavka" href="index.php?stranica=admin-kategorije"<?= ($admin_stavka ?? '') === 'kategorije' ? ' aria-current="page"' : '' ?>>Kategorije</a>
                <?php endif; ?>
            </nav>

            <h1 class="admin-naslov"><?= e($naslov ?? 'Administracija') ?></h1>
        <?php endif; ?>

        <?php $poruka = uzmi_poruku(); ?>
        <?php if ($poruka !== null): ?>
            <p class="poruka poruka--<?= e($poruka['vrsta']) ?>" role="<?= $poruka['vrsta'] === 'greska' ? 'alert' : 'status' ?>"><?= e($poruka['tekst']) ?></p>
        <?php endif; ?>

        <?= $sadrzaj ?>
    </main>

    <footer class="podnozje">
        <div>
            <p class="podnozje__naziv">Mjestopis</p>
            <p>Turistički vodič po Hrvatskoj</p>
        </div>
        <p>&copy; <?= date('Y') ?> Mjestopis</p>
    </footer>
</div>
</body>
</html>
