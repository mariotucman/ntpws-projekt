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
    <header class="vrh<?= isset($natpis) ? ' vrh--pocetna' : '' ?>">
        <div class="zaglavlje">
            <a class="logo" href="index.php">Mjestopis</a>

            <nav class="izbornik" aria-label="Glavni izbornik">
                <a class="izbornik__stavka" href="index.php"<?= ($aktivno ?? '') === 'pocetna' ? ' aria-current="page"' : '' ?>>Početna</a>
            </nav>

            <a class="zaglavlje__registracija" href="index.php?stranica=registracija"<?= ($aktivno ?? '') === 'registracija' ? ' aria-current="page"' : '' ?>>Registracija</a>
        </div>

        <div>
            <?php if (isset($natpis)): ?>
                <p class="vrh__natpis"><?= e($natpis) ?></p>
            <?php endif; ?>
            <h1 class="vrh__naslov"><?= e($naslov ?? 'Mjestopis') ?></h1>
            <?php if (isset($opis)): ?>
                <p class="vrh__opis"><?= e($opis) ?></p>
            <?php endif; ?>
        </div>
    </header>

    <main class="sadrzaj">
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
