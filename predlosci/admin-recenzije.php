<?php
$naslov = 'Recenzije';
$aktivno = 'administracija';
$admin_stavka = 'recenzije';
?>
<form class="filtar" method="get" action="index.php">
    <input type="hidden" name="stranica" value="admin-recenzije">

    <div class="polje">
        <label class="polje__oznaka" for="status">Prikaži</label>
        <select class="polje__unos" id="status" name="status">
            <?php foreach (FILTRI_RECENZIJA as $vrijednost => $naziv_filtra): ?>
                <option value="<?= e($vrijednost) ?>"<?= $vrijednost === $status ? ' selected' : '' ?>><?= e($naziv_filtra) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="filtar__radnje">
        <button class="gumb gumb--glavni" type="submit">Prikaži</button>
    </div>
</form>

<section class="admin-kartica">
    <p class="polje__pomoc">Recenzije se objavljuju odmah. Skrivena recenzija ne vidi se javno i ne ulazi u prosjek, ali je autor i dalje vidi kod sebe.</p>

    <?php if (count($recenzije) === 0): ?>
        <p class="poruka poruka--prazno"><?= $status === 'skrivene' ? 'Nema skrivenih recenzija.' : 'Još nema nijedne recenzije.' ?></p>
    <?php else: ?>
        <div class="tablica-omotac">
            <table class="tablica">
                <thead>
                    <tr>
                        <th scope="col">Datum</th>
                        <th scope="col">Lokacija</th>
                        <th scope="col">Autor</th>
                        <th scope="col">Ocjena</th>
                        <th scope="col">Komentar</th>
                        <th scope="col">Status</th>
                        <th scope="col">Radnje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recenzije as $recenzija): ?>
                        <?php $skrivena = (int) $recenzija['skriveno'] === 1; ?>
                        <tr>
                            <td><time datetime="<?= e(datum($recenzija['kreirano'], 'Y-m-d')) ?>"><?= e(datum($recenzija['kreirano'])) ?></time></td>
                            <td>
                                <?php if ((int) $recenzija['lokacija_objavljena'] === 1): ?>
                                    <a href="index.php?stranica=lokacija&amp;id=<?= e($recenzija['lokacija_id']) ?>#recenzije"><?= e($recenzija['lokacija']) ?></a>
                                <?php else: ?>
                                    <?= e($recenzija['lokacija']) ?> (skrivena lokacija)
                                <?php endif; ?>
                            </td>
                            <td><?= e($recenzija['autor']) ?></td>
                            <td>&#9733; <?= e($recenzija['ocjena']) ?></td>
                            <td><?= $recenzija['tekst'] !== null ? e(mb_strimwidth($recenzija['tekst'], 0, 120, '…')) : '&ndash;' ?></td>
                            <td>
                                <?php if ($skrivena): ?>
                                    <span class="oznaka oznaka--status">Skrivena</span>
                                <?php else: ?>
                                    <span class="oznaka oznaka--status oznaka--objavljeno">Vidljiva</span>
                                <?php endif; ?>
                            </td>
                            <td class="tablica__radnje">
                                <form method="post" action="index.php?stranica=admin-recenzija-skrivenost&amp;id=<?= e($recenzija['id']) ?>">
                                    <?= csrf_polje() ?>
                                    <!-- Nakon promjene vraća se na isti filtar i stranicu popisa -->
                                    <input type="hidden" name="status" value="<?= e($status) ?>">
                                    <input type="hidden" name="str" value="<?= e($trenutna_stranica) ?>">
                                    <input type="hidden" name="skriveno" value="<?= $skrivena ? '0' : '1' ?>">
                                    <button class="tablica__gumb<?= $skrivena ? '' : ' tablica__obrisi' ?>" type="submit"><?= $skrivena ? 'Prikaži' : 'Sakrij' ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php require __DIR__ . '/dijelovi/stranicenje.php'; ?>
    <?php endif; ?>
</section>
