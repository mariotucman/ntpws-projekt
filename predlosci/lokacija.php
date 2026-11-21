<?php
$naslov = $lokacija['naziv'];
$opis = $lokacija['grad_opcina'] . ', ' . $lokacija['zupanija'];
$aktivno = 'lokacije';
?>
<div class="galerija">
    <?php if (count($fotografije) === 0): ?>
        <img class="galerija__stavka galerija__slika" src="<?= e(adresa_slike_lokacije(null)) ?>" alt="">
    <?php endif; ?>
    <?php foreach ($fotografije as $broj => $fotografija): ?>
        <!-- Klik otvara fotografiju u punoj veličini, a uz JavaScript u povećanju preko stranice -->
        <a class="galerija__stavka" href="<?= e(adresa_slike_lokacije($fotografija['id'])) ?>">
            <img class="galerija__slika" src="<?= e(adresa_slike_lokacije($fotografija['id'])) ?>" width="<?= e($fotografija['sirina']) ?>" height="<?= e($fotografija['visina']) ?>"
                alt="<?= e($lokacija['naziv']) ?>, fotografija <?= $broj + 1 ?>"<?= $broj > 0 ? ' loading="lazy"' : '' ?>>
        </a>
    <?php endforeach; ?>
</div>

<?php if (count($fotografije) > 0): ?>
    <dialog class="povecanje">
        <div class="povecanje__vrh">
            <p class="povecanje__brojac"></p>
            <button class="povecanje__gumb povecanje__gumb--zatvori" type="button">Zatvori</button>
        </div>

        <img class="povecanje__slika" src="" alt="">

        <?php if (count($fotografije) > 1): ?>
            <div class="povecanje__dno">
                <button class="povecanje__gumb povecanje__gumb--prethodna" type="button">&larr; Prethodna</button>
                <button class="povecanje__gumb povecanje__gumb--sljedeca" type="button">Sljedeća &rarr;</button>
            </div>
        <?php endif; ?>
    </dialog>

    <script src="js/galerija.js" defer></script>
<?php endif; ?>

<div class="dva-stupca">
    <div class="dva-stupca__glavni">
        <article class="lokacija">
            <p><span class="oznaka oznaka--vrsta"><?= e($lokacija['vrsta']) ?></span></p>

            <h2 class="lokacija__naslov">O lokaciji</h2>

            <div class="lokacija__opis">
                <?= nl2br(e($lokacija['opis'])) ?>
            </div>

            <p class="lokacija__dno"><a href="index.php?stranica=lokacije">&larr; Sve lokacije</a></p>
        </article>

        <section class="recenzije" id="recenzije">
            <h2 class="recenzije__naslov">Recenzije</h2>

            <?php if ($smije_recenzirati): ?>
                <form class="recenzija-obrazac" method="post" action="index.php?stranica=lokacija&amp;id=<?= e($lokacija['id']) ?>#recenzije" novalidate>
                    <?= csrf_polje() ?>

                    <h3 class="recenzija-obrazac__naslov"><?= $vlastita === null ? 'Ocijeni lokaciju' : 'Tvoja recenzija' ?></h3>

                    <?php if ($vlastita !== null && (int) $vlastita['skriveno'] === 1): ?>
                        <p class="poruka poruka--greska">Recenziju je sakrio urednik pa je ostali ne vide. Ti je i dalje možeš urediti ili obrisati.</p>
                    <?php endif; ?>

                    <!-- Ocjena se bira klikom na broj od 1 do 5 -->
                    <fieldset class="polje ocjena-unos">
                        <legend class="polje__oznaka">Ocjena</legend>
                        <div class="ocjena-unos__izbor">
                            <?php for ($broj = 1; $broj <= 5; $broj++): ?>
                                <label class="ocjena-unos__pilula">
                                    <input type="radio" name="ocjena" value="<?= $broj ?>"<?= $obrazac['ocjena'] === $broj ? ' checked' : '' ?> required>
                                    <?= $broj ?>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <?php if (isset($greske['ocjena'])): ?>
                            <p class="polje__greska"><?= e($greske['ocjena']) ?></p>
                        <?php endif; ?>
                    </fieldset>

                    <div class="polje">
                        <label class="polje__oznaka" for="tekst-recenzije">Komentar (nije obavezan)</label>
                        <textarea class="polje__unos<?= isset($greske['tekst']) ? ' polje__unos--greska' : '' ?>" id="tekst-recenzije" name="tekst" rows="4" maxlength="<?= NAJVECI_TEKST_RECENZIJE ?>"><?= e($obrazac['tekst']) ?></textarea>
                        <?php if (isset($greske['tekst'])): ?>
                            <p class="polje__greska"><?= e($greske['tekst']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="recenzija-obrazac__radnje">
                        <button class="gumb gumb--glavni" type="submit"><?= $vlastita === null ? 'Objavi recenziju' : 'Spremi promjene' ?></button>
                        <?php if ($vlastita !== null): ?>
                            <!-- Gumb pripada zasebnom obrascu za brisanje (atribut form) -->
                            <button class="gumb gumb--obrub" type="submit" form="obrisi-recenziju">Obriši recenziju</button>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if ($vlastita !== null): ?>
                    <form id="obrisi-recenziju" method="post" action="index.php?stranica=recenzija-obrisi&amp;lokacija=<?= e($lokacija['id']) ?>">
                        <?= csrf_polje() ?>
                    </form>
                <?php endif; ?>
            <?php elseif (!$prijavljen): ?>
                <p class="recenzije__prijava"><a href="index.php?stranica=prijava">Prijavi se</a> da ocijeniš ovu lokaciju.</p>
            <?php endif; ?>

            <?php if ($sazetak['broj'] === 0): ?>
                <p class="poruka poruka--prazno">Još nema recenzija za ovu lokaciju.</p>
            <?php else: ?>
                <div class="recenzije__sazetak">
                    <div>
                        <p class="recenzije__prosjek"><?= e(ocjena($sazetak['prosjek'])) ?></p>
                        <p class="recenzije__broj"><?= e($sazetak['broj']) ?> recenzija</p>
                    </div>

                    <!-- Raspodjela ocjena: duljina trake je udio te ocjene u svim recenzijama -->
                    <dl class="recenzije__raspodjela">
                        <?php foreach ($sazetak['raspodjela'] as $broj_zvjezdica => $koliko): ?>
                            <div class="recenzije__traka">
                                <dt><?= $broj_zvjezdica ?></dt>
                                <dd class="recenzije__udio" style="--udio: <?= round($koliko / $sazetak['broj'] * 100) ?>%"></dd>
                                <dd><?= e($koliko) ?></dd>
                            </div>
                        <?php endforeach; ?>
                    </dl>
                </div>

                <?php foreach ($recenzije as $recenzija): ?>
                    <article class="recenzija">
                        <span class="recenzija__inicijal"><?= e(inicijali($recenzija['autor'])) ?></span>
                        <div class="recenzija__sadrzaj">
                            <p class="recenzija__vrh">
                                <strong class="recenzija__autor"><?= e($recenzija['autor']) ?></strong>
                                <time class="recenzija__datum" datetime="<?= e(datum($recenzija['kreirano'], 'Y-m-d')) ?>"><?= e(datum($recenzija['kreirano'])) ?></time>
                                <span class="recenzija__ocjena">&#9733; <?= e($recenzija['ocjena']) ?></span>
                            </p>
                            <?php if ($recenzija['tekst'] !== null): ?>
                                <p class="recenzija__tekst"><?= nl2br(e($recenzija['tekst'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>

                <?php require __DIR__ . '/dijelovi/stranicenje.php'; ?>
            <?php endif; ?>
        </section>
    </div>

    <aside class="dva-stupca__bocni">
        <section class="bocna-kartica">
            <h2 class="bocna-kartica__naslov">Radno vrijeme</h2>

            <?php if ($radno_vrijeme === null): ?>
                <p class="bocna-kartica__istaknuto">Uvijek otvoreno</p>
            <?php else: ?>
                <dl class="radno-vrijeme">
                    <?php foreach ($radno_vrijeme['dani'] as [$dan, $vrijeme, $danas]): ?>
                        <div class="radno-vrijeme__dan<?= $danas ? ' radno-vrijeme__dan--danas' : '' ?>">
                            <dt><?= e($dan) ?></dt>
                            <dd><?= e($vrijeme) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>

                <?php if ($radno_vrijeme['napomena'] !== null): ?>
                    <p class="bocna-kartica__napomena"><?= e($radno_vrijeme['napomena']) ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <?php if ($lokacija['adresa'] !== null): ?>
            <section class="bocna-kartica">
                <h2 class="bocna-kartica__naslov">Adresa</h2>
                <p><?= e($lokacija['adresa']) ?><br><?= e($lokacija['grad_opcina']) ?></p>
            </section>
        <?php endif; ?>
    </aside>
</div>
