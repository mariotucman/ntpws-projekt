<?php
// Stranice administracije imaju samo zaglavlje, a naslov stranice je u sadržaju
$aktivno ??= '';
$admin = $aktivno === 'administracija';
$prijavljeni = trenutni_korisnik();

// Klase poveznice: stavka stranice na kojoj se korisnik nalazi dobiva i klasu --trenutna
$klase = function ($blok, $stranica) use ($aktivno) {
    return $aktivno === $stranica ? $blok . ' ' . $blok . '--trenutna' : $blok;
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

            <nav class="izbornik">
                <a class="<?= $klase('izbornik__stavka', 'pocetna') ?>" href="index.php">Početna</a>
                <a class="<?= $klase('izbornik__stavka', 'lokacije') ?>" href="index.php?stranica=lokacije">Lokacije</a>
                <a class="<?= $klase('izbornik__stavka', 'dogadanja') ?>" href="index.php?stranica=dogadanja">Događanja</a>
                <a class="<?= $klase('izbornik__stavka', 'vijesti') ?>" href="index.php?stranica=vijesti">Vijesti</a>
                <?php if ($prijavljeni !== null): ?>
                    <a class="<?= $klase('izbornik__stavka', 'moj-plan') ?>" href="index.php?stranica=moj-plan">Moj plan</a>
                <?php endif; ?>
                <?php if (ima_ulogu(['administrator', 'urednik'])): ?>
                    <a class="<?= $klase('izbornik__stavka', 'administracija') ?>" href="index.php?stranica=admin-vijesti">Administracija</a>
                <?php endif; ?>
            </nav>

            <div class="racun">
                <?php if ($prijavljeni !== null): ?>
                    <a class="racun__korisnik" href="index.php?stranica=profil">
                        <span class="racun__inicijali"><?= e(inicijali($prijavljeni['ime'])) ?></span>
                        <?= e($prijavljeni['ime']) ?>
                    </a>
                    <form method="post" action="index.php?stranica=odjava">
                        <?= csrf_polje() ?>
                        <button class="racun__gumb" type="submit">Odjava</button>
                    </form>
                <?php else: ?>
                    <a class="<?= $klase('racun__gumb', 'prijava') ?>" href="index.php?stranica=prijava">Prijava</a>
                    <a class="<?= $klase('racun__gumb', 'registracija') ?> racun__gumb--naglasak" href="index.php?stranica=registracija">Registracija</a>
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
            <nav class="admin-izbornik">
                <a class="admin-izbornik__stavka<?= ($admin_stavka ?? '') === 'vijesti' ? ' admin-izbornik__stavka--trenutna' : '' ?>" href="index.php?stranica=admin-vijesti">Vijesti</a>
                <?php if (ima_ulogu(['administrator'])): ?>
                    <a class="admin-izbornik__stavka<?= ($admin_stavka ?? '') === 'kategorije' ? ' admin-izbornik__stavka--trenutna' : '' ?>" href="index.php?stranica=admin-kategorije">Kategorije</a>
                <?php endif; ?>
                <a class="admin-izbornik__stavka<?= ($admin_stavka ?? '') === 'lokacije' ? ' admin-izbornik__stavka--trenutna' : '' ?>" href="index.php?stranica=admin-lokacije">Lokacije</a>
                <?php if (ima_ulogu(['administrator'])): ?>
                    <a class="admin-izbornik__stavka<?= ($admin_stavka ?? '') === 'vrste-lokacija' ? ' admin-izbornik__stavka--trenutna' : '' ?>" href="index.php?stranica=admin-vrste-lokacija">Vrste lokacija</a>
                <?php endif; ?>
                <a class="admin-izbornik__stavka<?= ($admin_stavka ?? '') === 'recenzije' ? ' admin-izbornik__stavka--trenutna' : '' ?>" href="index.php?stranica=admin-recenzije">Recenzije</a>
                <a class="admin-izbornik__stavka<?= ($admin_stavka ?? '') === 'dogadanja' ? ' admin-izbornik__stavka--trenutna' : '' ?>" href="index.php?stranica=admin-dogadanja">Događanja</a>
                <?php if (ima_ulogu(['administrator'])): ?>
                    <a class="admin-izbornik__stavka<?= ($admin_stavka ?? '') === 'vrste-dogadanja' ? ' admin-izbornik__stavka--trenutna' : '' ?>" href="index.php?stranica=admin-vrste-dogadanja">Vrste događanja</a>
                    <a class="admin-izbornik__stavka<?= ($admin_stavka ?? '') === 'korisnici' ? ' admin-izbornik__stavka--trenutna' : '' ?>" href="index.php?stranica=admin-korisnici">Korisnici</a>
                <?php endif; ?>
            </nav>

            <h1 class="admin-naslov"><?= e($naslov ?? 'Administracija') ?></h1>
        <?php endif; ?>

        <?php $poruka = uzmi_poruku(); ?>
        <?php if ($poruka !== null): ?>
            <p class="poruka poruka--<?= e($poruka['vrsta']) ?>"><?= e($poruka['tekst']) ?></p>
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
