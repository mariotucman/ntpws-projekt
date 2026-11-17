<?php
$naslov = 'Kategorije vijesti';
$aktivno = 'administracija';
?>
<section class="admin-kartica">
    <?php if (count($kategorije) === 0): ?>
        <p class="poruka poruka--prazno">Još nema nijedne kategorije.</p>
    <?php else: ?>
        <div class="tablica-omotac">
            <table class="tablica">
                <thead>
                    <tr>
                        <th scope="col">Naziv</th>
                        <th scope="col">Broj vijesti</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kategorije as $kategorija): ?>
                        <tr>
                            <td><?= e($kategorija['naziv']) ?></td>
                            <td><?= e($kategorija['broj_vijesti']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
