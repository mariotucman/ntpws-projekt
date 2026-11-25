<?php
// Kartica događanja (popis događanja i početna). Treba $dogadanje iz dogadanja_javna() i redni $broj u popisu.

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
