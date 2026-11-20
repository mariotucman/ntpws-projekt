<?php
$naslov = 'Vrste lokacija';
$aktivno = 'administracija';
$admin_stavka = 'vrste-lokacija';
?>
<section class="admin-kartica">
    <p><a class="gumb gumb--glavni" href="index.php?stranica=admin-vrsta-lokacije">Nova vrsta</a></p>

    <?php if (count($vrste) === 0): ?>
        <p class="poruka poruka--prazno">Još nema nijedne vrste lokacija.</p>
    <?php else: ?>
        <div class="tablica-omotac">
            <table class="tablica">
                <thead>
                    <tr>
                        <th scope="col">Naziv</th>
                        <th scope="col">Broj lokacija</th>
                        <th scope="col">Radnje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vrste as $vrsta): ?>
                        <tr>
                            <td><?= e($vrsta['naziv']) ?></td>
                            <td><?= e($vrsta['broj_lokacija']) ?></td>
                            <td class="tablica__radnje">
                                <a href="index.php?stranica=admin-vrsta-lokacije&amp;id=<?= e($vrsta['id']) ?>">Uredi</a>
                                <a class="tablica__obrisi" href="index.php?stranica=admin-vrsta-lokacije-obrisi&amp;id=<?= e($vrsta['id']) ?>">Obriši</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
