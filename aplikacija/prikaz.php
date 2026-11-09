<?php

function prikazi($predlozak, $podaci = [])
{
    extract($podaci);

    ob_start();
    require __DIR__ . '/../predlosci/' . $predlozak . '.php';
    $sadrzaj = ob_get_clean();

    require __DIR__ . '/../predlosci/izgled.php';
}

// Zaštita teksta za ispis u HTML-u
function e($tekst)
{
    return htmlspecialchars((string) $tekst, ENT_QUOTES, 'UTF-8');
}
