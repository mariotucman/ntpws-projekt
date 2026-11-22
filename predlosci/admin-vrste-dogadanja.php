<?php
$naslov = 'Vrste događanja';
$aktivno = 'administracija';
$admin_stavka = 'vrste-dogadanja';
?>
<section class="admin-kartica">
    <p><a class="gumb gumb--glavni" href="index.php?stranica=admin-vrsta-dogadanja">Nova vrsta</a></p>

    <?php if (count($vrste) === 0): ?>
        <p class="poruka poruka--prazno">Još nema nijedne vrste događanja.</p>
    <?php else: ?>
        <div class="tablica-omotac">
            <table class="tablica">
                <thead>
                    <tr>
                        <th scope="col">Naziv</th>
                        <th scope="col">Broj događanja</th>
                        <th scope="col">Radnje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vrste as $vrsta): ?>
                        <tr>
                            <td><?= e($vrsta['naziv']) ?></td>
                            <td><?= e($vrsta['broj_dogadanja']) ?></td>
                            <td class="tablica__radnje">
                                <a href="index.php?stranica=admin-vrsta-dogadanja&amp;id=<?= e($vrsta['id']) ?>">Uredi</a>
                                <a class="tablica__obrisi" href="index.php?stranica=admin-vrsta-dogadanja-obrisi&amp;id=<?= e($vrsta['id']) ?>">Obriši</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
