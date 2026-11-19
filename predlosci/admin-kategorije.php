<?php
$naslov = 'Kategorije vijesti';
$aktivno = 'administracija';
$admin_stavka = 'kategorije';
?>
<section class="admin-kartica">
    <p><a class="gumb gumb--glavni" href="index.php?stranica=admin-kategorija">Nova kategorija</a></p>

    <?php if (count($kategorije) === 0): ?>
        <p class="poruka poruka--prazno">Još nema nijedne kategorije.</p>
    <?php else: ?>
        <div class="tablica-omotac">
            <table class="tablica">
                <thead>
                    <tr>
                        <th scope="col">Naziv</th>
                        <th scope="col">Broj vijesti</th>
                        <th scope="col">Radnje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kategorije as $kategorija): ?>
                        <tr>
                            <td><?= e($kategorija['naziv']) ?></td>
                            <td><?= e($kategorija['broj_vijesti']) ?></td>
                            <td class="tablica__radnje">
                                <a href="index.php?stranica=admin-kategorija&amp;id=<?= e($kategorija['id']) ?>">Uredi</a>
                                <a class="tablica__obrisi" href="index.php?stranica=admin-kategorija-obrisi&amp;id=<?= e($kategorija['id']) ?>">Obriši</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
