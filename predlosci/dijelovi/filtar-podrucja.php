<?php
// Zajednički filtar županija -> grad/općina
$nazivi_zupanija = array_column($zupanije, 'naziv', 'id');
$grupe = [];

// Tekst prazne opcije može se promijeniti (npr. u obrascu vijesti „Cijela Hrvatska”)
$oznaka_sve_zupanije ??= 'Sve županije';
$oznaka_svi_gradovi ??= 'Svi gradovi i općine';
foreach ($gradovi_opcine as $grad_opcina) {
    $grupe[$grad_opcina['zupanija_id']][] = $grad_opcina;
}
?>
<div class="polje">
    <label class="polje__oznaka" for="zupanija">Županija</label>
    <select class="polje__unos" id="zupanija" name="zupanija">
        <option value=""><?= e($oznaka_sve_zupanije) ?></option>
        <?php foreach ($zupanije as $zupanija): ?>
            <option value="<?= e($zupanija['id']) ?>"<?= (int) $zupanija['id'] === $odabrana_zupanija ? ' selected' : '' ?>><?= e($zupanija['naziv']) ?></option>
        <?php endforeach; ?>
    </select>
</div>

<div class="polje">
    <label class="polje__oznaka" for="grad_opcina">Grad/općina</label>
    <select class="polje__unos" id="grad_opcina" name="grad_opcina">
        <option value=""><?= e($oznaka_svi_gradovi) ?></option>
        <?php foreach ($grupe as $zupanija_id => $gradovi): ?>
            <optgroup label="<?= e($nazivi_zupanija[$zupanija_id]) ?>" data-zupanija="<?= e($zupanija_id) ?>">
                <?php foreach ($gradovi as $grad_opcina): ?>
                    <option value="<?= e($grad_opcina['id']) ?>"<?= (int) $grad_opcina['id'] === $odabrani_grad_opcina ? ' selected' : '' ?>><?= e($grad_opcina['naziv']) ?></option>
                <?php endforeach; ?>
            </optgroup>
        <?php endforeach; ?>
    </select>
</div>

<script src="js/filtar-podrucja.js" defer></script>
