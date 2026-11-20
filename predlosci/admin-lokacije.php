<?php
$naslov = 'Lokacije';
$aktivno = 'administracija';
$admin_stavka = 'lokacije';
?>
<form class="filtar" method="get" action="index.php">
    <input type="hidden" name="stranica" value="admin-lokacije">

    <div class="polje">
        <label class="polje__oznaka" for="trazi">Pretraga po nazivu ili gradu/općini</label>
        <input class="polje__unos" type="search" id="trazi" name="trazi" value="<?= e($trazi) ?>" maxlength="100">
    </div>

    <div class="filtar__radnje">
        <button class="gumb gumb--glavni" type="submit">Traži</button>
        <?php if ($trazi !== ''): ?>
            <a class="gumb gumb--obrub" href="index.php?stranica=admin-lokacije">Poništi pretragu</a>
        <?php endif; ?>
    </div>
</form>

<section class="admin-kartica">
    <p><a class="gumb gumb--glavni" href="index.php?stranica=admin-lokacija">Nova lokacija</a></p>

    <?php if (count($lokacije) === 0): ?>
        <p class="poruka poruka--prazno"><?= $trazi !== '' ? 'Nema lokacija za pretragu „' . e($trazi) . '”.' : 'Još nema nijedne lokacije.' ?></p>
    <?php else: ?>
        <div class="tablica-omotac">
            <table class="tablica">
                <thead>
                    <tr>
                        <th scope="col">Naziv</th>
                        <th scope="col">Vrsta</th>
                        <th scope="col">Grad/općina</th>
                        <th scope="col">Autor</th>
                        <th scope="col">Status</th>
                        <th scope="col">Zadnja izmjena</th>
                        <th scope="col">Radnje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lokacije as $lokacija): ?>
                        <tr>
                            <td><?= e($lokacija['naziv']) ?></td>
                            <td><?= e($lokacija['vrsta']) ?></td>
                            <td><?= e($lokacija['grad_opcina']) ?></td>
                            <td><?= e($lokacija['autor']) ?></td>
                            <td>
                                <?php if ((int) $lokacija['objavljeno'] === 1): ?>
                                    <span class="oznaka oznaka--status oznaka--objavljeno">Objavljeno</span>
                                <?php else: ?>
                                    <span class="oznaka oznaka--status">Skriveno</span>
                                <?php endif; ?>
                                <?php if ((int) $lokacija['istaknuto'] === 1): ?>
                                    <span class="oznaka oznaka--status">Istaknuto</span>
                                <?php endif; ?>
                            </td>
                            <td><time datetime="<?= e(datum($lokacija['azurirano'], 'Y-m-d')) ?>"><?= e(datum($lokacija['azurirano'])) ?></time></td>
                            <td class="tablica__radnje">
                                <a href="index.php?stranica=admin-lokacija&amp;id=<?= e($lokacija['id']) ?>">Uredi</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php require __DIR__ . '/dijelovi/stranicenje.php'; ?>
    <?php endif; ?>
</section>
