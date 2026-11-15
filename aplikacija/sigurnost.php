<?php
// Zaštita obrazaca od CSRF napada.
// Svaka sesija dobiva tajni token. Naši obrasci ga šalju u skrivenom polju, a obrazac
// s tuđe stranice ga ne može znati, pa poslužitelj takav zahtjev odbija.

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

// Skriveno polje koje se stavlja u svaki obrazac koji šalje POST.
function csrf_polje()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

// Vraća true ako je poslani token jednak tokenu iz sesije.
function csrf_ispravan()
{
    $poslani = post_tekst('csrf_token');

    return $poslani !== '' && hash_equals(csrf_token(), $poslani);
}
