<?php
$naslov = 'Korisnici';
$aktivno = 'administracija';
$admin_stavka = 'korisnici';
?>
<form class="filtar" method="get" action="index.php">
    <input type="hidden" name="stranica" value="admin-korisnici">

    <div class="polje">
        <label class="polje__oznaka" for="trazi">Pretraga po imenu ili emailu</label>
        <input class="polje__unos" type="search" id="trazi" name="trazi" value="<?= e($trazi) ?>" maxlength="100">
    </div>

    <div class="filtar__radnje">
        <button class="gumb gumb--glavni" type="submit">Traži</button>
        <?php if ($trazi !== ''): ?>
            <a class="gumb gumb--obrub" href="index.php?stranica=admin-korisnici">Poništi pretragu</a>
        <?php endif; ?>
    </div>
</form>

<section class="admin-kartica">
    <?php if (count($korisnici) === 0): ?>
        <p class="poruka poruka--prazno"><?= $trazi !== '' ? 'Nema korisnika za pretragu „' . e($trazi) . '”.' : 'Još nema nijednog korisnika.' ?></p>
    <?php else: ?>
        <div class="tablica-omotac">
            <table class="tablica">
                <thead>
                    <tr>
                        <th scope="col">Ime</th>
                        <th scope="col">Email</th>
                        <th scope="col">Uloga</th>
                        <th scope="col">Račun</th>
                        <th scope="col">Registriran</th>
                        <th scope="col">Broj vijesti</th>
                        <th scope="col">Radnje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($korisnici as $korisnik): ?>
                        <tr>
                            <td><?= e($korisnik['ime']) ?></td>
                            <td><?= e($korisnik['email']) ?></td>
                            <td><?= e($korisnik['uloga']) ?></td>
                            <td>
                                <?php if ($korisnik['aktivan']): ?>
                                    <span class="oznaka oznaka--aktivan">Aktivan</span>
                                <?php else: ?>
                                    <span class="oznaka oznaka--status">Deaktiviran</span>
                                <?php endif; ?>
                            </td>
                            <td><time datetime="<?= e(datum($korisnik['kreirano'], 'Y-m-d')) ?>"><?= e(datum($korisnik['kreirano'])) ?></time></td>
                            <td><?= e($korisnik['broj_vijesti']) ?></td>
                            <td class="tablica__radnje">
                                <?php if ((int) $korisnik['id'] === $prijavljeni_id): ?>
                                    (ti)
                                <?php else: ?>
                                    <a href="index.php?stranica=admin-korisnik&amp;id=<?= e($korisnik['id']) ?>">Uredi</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php require __DIR__ . '/dijelovi/stranicenje.php'; ?>
    <?php endif; ?>
</section>
