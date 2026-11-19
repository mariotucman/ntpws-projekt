<?php
$naslov = 'Uredi korisnika';
$aktivno = 'administracija';
$admin_stavka = 'korisnici';
?>
<form class="obrazac-kartica" method="post" action="index.php?stranica=admin-korisnik&amp;id=<?= e($korisnik['id']) ?>" novalidate>
    <?= csrf_polje() ?>

    <div>
        <p class="obrazac-kartica__naslov"><?= e($korisnik['ime']) ?></p>
        <p class="polje__pomoc"><?= e($korisnik['email']) ?> &middot; registriran <?= e(datum($korisnik['kreirano'])) ?></p>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="uloga">Uloga</label>
        <select class="polje__unos<?= isset($greske['uloga']) ? ' polje__unos--greska' : '' ?>" id="uloga" name="uloga">
            <?php foreach ($uloge as $moguca): ?>
                <option value="<?= e($moguca['oznaka']) ?>"<?= $moguca['oznaka'] === $uloga ? ' selected' : '' ?>><?= e($moguca['naziv']) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($greske['uloga'])): ?>
            <p class="polje__greska"><?= e($greske['uloga']) ?></p>
        <?php endif; ?>
    </div>

    <div class="polje">
        <label class="polje__kvacica">
            <input type="checkbox" name="aktivan" value="1"<?= $aktivan ? ' checked' : '' ?>>
            Račun je aktivan
        </label>
        <p class="polje__pomoc">Deaktivirani korisnik se odmah odjavljuje i ne može se ponovno prijaviti.</p>
    </div>

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Spremi</button>

    <p class="obrazac-kartica__dno"><a href="index.php?stranica=admin-korisnici">Natrag na korisnike</a></p>
</form>
