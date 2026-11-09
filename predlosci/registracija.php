<?php
$naslov = 'Registracija';
$opis = 'Napravi račun i spremaj lokacije, piši recenzije i složi svoj plan puta.';
$aktivno = 'registracija';
?>
<form class="obrazac-kartica" method="post" action="index.php?stranica=registracija">
    <h2 class="obrazac-kartica__naslov">Napravi račun</h2>

    <div class="polje">
        <label class="polje__oznaka" for="ime">Ime</label>
        <input class="polje__unos" type="text" id="ime" name="ime" autocomplete="given-name" required>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="email">Email</label>
        <input class="polje__unos" type="email" id="email" name="email" autocomplete="email" required>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="lozinka">Lozinka</label>
        <input class="polje__unos" type="password" id="lozinka" name="lozinka" autocomplete="new-password" aria-describedby="lozinka-pomoc" required>
        <p class="polje__pomoc" id="lozinka-pomoc">Najmanje 8 znakova.</p>
    </div>

    <div class="polje">
        <label class="polje__oznaka" for="lozinka_ponovo">Ponovi lozinku</label>
        <input class="polje__unos" type="password" id="lozinka_ponovo" name="lozinka_ponovo" autocomplete="new-password" required>
    </div>

    <button class="gumb gumb--glavni obrazac-kartica__gumb" type="submit">Registriraj se</button>
</form>
