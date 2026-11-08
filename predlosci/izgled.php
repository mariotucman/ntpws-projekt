<!doctype html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($naslov ?? 'Mjestopis') ?> | Mjestopis</title>
    <link rel="stylesheet" href="css/stil.css">
</head>
<body>
    <header>
        <a href="index.php">Mjestopis</a>
        <nav>
            <a href="index.php">Početna</a>
        </nav>
    </header>

    <main>
        <?php require __DIR__ . '/' . $predlozak . '.php'; ?>
    </main>

    <footer>
        <p>Mjestopis <?= date('Y') ?></p>
    </footer>
</body>
</html>
