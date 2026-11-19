<?php
$naslov = 'Vijesti';
$aktivno = 'administracija';
$admin_stavka = 'vijesti';
?>
<form class="filtar" method="get" action="index.php">
    <input type="hidden" name="stranica" value="admin-vijesti">

    <div class="polje">
        <label class="polje__oznaka" for="trazi">Pretraga po naslovu ili sažetku</label>
        <input class="polje__unos" type="search" id="trazi" name="trazi" value="<?= e($trazi) ?>" maxlength="100">
    </div>

    <div class="filtar__radnje">
        <button class="gumb gumb--glavni" type="submit">Traži</button>
        <?php if ($trazi !== ''): ?>
            <a class="gumb gumb--obrub" href="index.php?stranica=admin-vijesti">Poništi pretragu</a>
        <?php endif; ?>
    </div>
</form>

<section class="admin-kartica">
    <p><a class="gumb gumb--glavni" href="index.php?stranica=admin-vijest">Nova vijest</a></p>

    <?php if (count($vijesti) === 0): ?>
        <p class="poruka poruka--prazno"><?= $trazi !== '' ? 'Nema vijesti za pretragu „' . e($trazi) . '”.' : 'Još nema nijedne vijesti.' ?></p>
    <?php else: ?>
        <div class="tablica-omotac">
            <table class="tablica">
                <thead>
                    <tr>
                        <th scope="col">Naslov</th>
                        <th scope="col">Kategorija</th>
                        <th scope="col">Područje</th>
                        <th scope="col">Autor</th>
                        <th scope="col">Status</th>
                        <th scope="col">Objava</th>
                        <th scope="col">Radnje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vijesti as $vijest): ?>
                        <?php
                        // Objavljena vijest s datumom u budućnosti javno se još ne vidi
                        $status = $vijest['status'];
                        if ($status === 'objavljeno' && strtotime($vijest['objavljeno_u']) > time()) {
                            $status = 'zakazano';
                        }
                        ?>
                        <tr>
                            <td><?= e($vijest['naslov']) ?></td>
                            <td><?= e($vijest['kategorija']) ?></td>
                            <td><?= e($vijest['grad_opcina'] ?? 'Cijela Hrvatska') ?></td>
                            <td><?= e($vijest['autor']) ?></td>
                            <td><span class="oznaka oznaka--status<?= $status === 'objavljeno' ? ' oznaka--objavljeno' : '' ?>"><?= e(STATUSI_VIJESTI[$status]) ?></span></td>
                            <td>
                                <?php if ($vijest['objavljeno_u'] !== null): ?>
                                    <time datetime="<?= e(datum($vijest['objavljeno_u'], 'Y-m-d')) ?>"><?= e(datum($vijest['objavljeno_u'])) ?></time>
                                <?php else: ?>
                                    &ndash;
                                <?php endif; ?>
                            </td>
                            <td class="tablica__radnje">
                                <a href="index.php?stranica=admin-vijest&amp;id=<?= e($vijest['id']) ?>">Uredi</a>
                                <a class="tablica__obrisi" href="index.php?stranica=admin-vijest-obrisi&amp;id=<?= e($vijest['id']) ?>">Obriši</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php require __DIR__ . '/dijelovi/stranicenje.php'; ?>
    <?php endif; ?>
</section>
