<?php
$naslov = 'Događanja';
$opis = 'Koncerti, festivali, sajmovi i izložbe diljem Hrvatske';
$aktivno = 'dogadanja';
?>
<?php $filtrirano = $odabrana_zupanija > 0 || $odabrana_vrsta > 0 || $od !== '' || $do !== ''; ?>
<form class="filtar" method="get" action="index.php">
    <input type="hidden" name="stranica" value="dogadanja">

    <?php require __DIR__ . '/dijelovi/filtar-podrucja.php'; ?>

    <div class="polje">
        <label class="polje__oznaka" for="vrsta">Vrsta</label>
        <select class="polje__unos" id="vrsta" name="vrsta">
            <option value="">Sve vrste</option>
            <?php foreach ($vrste as $vrsta): ?>
                <option value="<?= e($vrsta['id']) ?>"<?= (int) $vrsta['id'] === $odabrana_vrsta ? ' selected' : '' ?>><?= e($vrsta['naziv']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="od">Od</label>
        <input class="polje__unos" type="date" id="od" name="od" value="<?= e($od) ?>" min="<?= e(date('Y-m-d')) ?>">
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="do">Do</label>
        <input class="polje__unos" type="date" id="do" name="do" value="<?= e($do) ?>" min="<?= e(date('Y-m-d')) ?>">
    </div>

    <div class="filtar__radnje">
        <button class="gumb gumb--glavni" type="submit">Prikaži</button>
        <?php if ($filtrirano): ?>
            <a class="gumb gumb--obrub" href="index.php?stranica=dogadanja">Poništi filtar</a>
        <?php endif; ?>
    </div>
</form>

<?php if (count($dogadanja) === 0): ?>
    <p class="poruka poruka--prazno"><?= $filtrirano ? 'Nema događanja za odabrani filtar.' : 'Trenutno nema nadolazećih događanja.' ?></p>
<?php else: ?>
    <?php $prethodni_dan = null; ?>
    <?php foreach ($dogadanja as $broj => $dogadanje): ?>
        <?php if ($dogadanje['dan'] !== $prethodni_dan): ?>
            <?php if ($prethodni_dan !== null): ?>
                </div>
            <?php endif; ?>
            <h2 class="dan-dogadanja"><?= e(naslov_dana($dogadanje['dan'])) ?></h2>
            <div class="popis-dogadanja">
            <?php $prethodni_dan = $dogadanje['dan']; ?>
        <?php endif; ?>

        <?php
        // Mjesto: lokacija iz baze ili upisana adresa
        $mjesto = $dogadanje['lokacija'] ?? $dogadanje['adresa'];
        // Događanje koje je već počelo ili traje barem 24 sata prikazuje do kada traje, a ostala vrijeme početka
        // (npr. doček od 21:00 do 1:00 prikazuje „21:00”, a ne „do 1. 1.”)
        $vise_dana = $dogadanje['zavrsetak'] !== null && (datum($dogadanje['pocetak'], 'Y-m-d') < $dogadanje['dan']
            || strtotime($dogadanje['zavrsetak']) - strtotime($dogadanje['pocetak']) >= 24 * 60 * 60);
        $vrijeme = $vise_dana ? 'do ' . datum($dogadanje['zavrsetak'], 'j. n.') : datum($dogadanje['pocetak'], 'H:i');
        ?>
        <article class="dogadanje">
            <!-- Blok datuma: boja se izmjenjuje kroz popis (1., 2., 3., pa ispočetka) -->
            <p class="dogadanje__datum dogadanje__datum--<?= $broj % 3 + 1 ?>">
                <span class="dogadanje__dan"><?= e(naziv_dana($dogadanje['dan'], true)) ?></span>
                <span class="dogadanje__broj"><?= e(datum($dogadanje['dan'], 'j')) ?></span>
            </p>
            <div class="dogadanje__sredina">
                <p class="dogadanje__vrsta"><?= e($dogadanje['vrsta'] . ' · ' . $dogadanje['grad_opcina']) ?></p>
                <h3 class="dogadanje__naziv"><a href="index.php?stranica=dogadanje&amp;id=<?= e($dogadanje['id']) ?>"><?= e($dogadanje['naziv']) ?></a></h3>
                <p class="dogadanje__podaci"><?= e(implode(' · ', array_filter([$mjesto, $vrijeme, cijena($dogadanje['cijena'])]))) ?></p>
            </div>
        </article>
    <?php endforeach; ?>
    </div>

    <?php require __DIR__ . '/dijelovi/stranicenje.php'; ?>
<?php endif; ?>
