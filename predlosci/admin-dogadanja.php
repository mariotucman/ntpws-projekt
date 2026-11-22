<?php
$naslov = 'Događanja';
$aktivno = 'administracija';
$admin_stavka = 'dogadanja';
?>
<form class="filtar" method="get" action="index.php">
    <input type="hidden" name="stranica" value="admin-dogadanja">

    <div class="polje">
        <label class="polje__oznaka" for="trazi">Pretraga po nazivu ili gradu/općini</label>
        <input class="polje__unos" type="search" id="trazi" name="trazi" value="<?= e($trazi) ?>" maxlength="100">
    </div>

    <div class="filtar__radnje">
        <button class="gumb gumb--glavni" type="submit">Traži</button>
        <?php if ($trazi !== ''): ?>
            <a class="gumb gumb--obrub" href="index.php?stranica=admin-dogadanja">Poništi pretragu</a>
        <?php endif; ?>
    </div>
</form>

<section class="admin-kartica">
    <p><a class="gumb gumb--glavni" href="index.php?stranica=admin-dogadanje">Novo događanje</a></p>

    <?php if (count($dogadanja) === 0): ?>
        <p class="poruka poruka--prazno"><?= $trazi !== '' ? 'Nema događanja za pretragu „' . e($trazi) . '”.' : 'Još nema nijednog događanja.' ?></p>
    <?php else: ?>
        <div class="tablica-omotac">
            <table class="tablica">
                <thead>
                    <tr>
                        <th scope="col">Naziv</th>
                        <th scope="col">Vrsta</th>
                        <th scope="col">Grad/općina</th>
                        <th scope="col">Početak</th>
                        <th scope="col">Autor</th>
                        <th scope="col">Status</th>
                        <th scope="col">Radnje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dogadanja as $dogadanje): ?>
                        <?php $zavrsilo = strtotime($dogadanje['zavrsetak'] ?? datum($dogadanje['pocetak'], 'Y-m-d 23:59:59')) < time(); ?>
                        <tr>
                            <td><?= e($dogadanje['naziv']) ?></td>
                            <td><?= e($dogadanje['vrsta']) ?></td>
                            <td><?= e($dogadanje['grad_opcina']) ?></td>
                            <td><time datetime="<?= e(datum($dogadanje['pocetak'], 'Y-m-d\TH:i')) ?>"><?= e(datum($dogadanje['pocetak'], 'j. n. Y. H:i')) ?></time></td>
                            <td><?= e($dogadanje['autor']) ?></td>
                            <td>
                                <?php if ((int) $dogadanje['objavljeno'] === 1): ?>
                                    <span class="oznaka oznaka--status oznaka--objavljeno">Objavljeno</span>
                                <?php else: ?>
                                    <span class="oznaka oznaka--status">Skriveno</span>
                                <?php endif; ?>
                                <?php if ((int) $dogadanje['istaknuto'] === 1): ?>
                                    <span class="oznaka oznaka--status">Istaknuto</span>
                                <?php endif; ?>
                                <?php if ($zavrsilo): ?>
                                    <span class="oznaka oznaka--status">Završilo</span>
                                <?php endif; ?>
                            </td>
                            <td class="tablica__radnje">
                                <a href="index.php?stranica=admin-dogadanje&amp;id=<?= e($dogadanje['id']) ?>">Uredi</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php require __DIR__ . '/dijelovi/stranicenje.php'; ?>
    <?php endif; ?>
</section>
