<?php
// Tokeni za prijavu u REST API. U bazi je samo SHA-256 sažetak tokena.

// Stvara novi token za korisnika i vraća ga (jedini put kad se vidi u izvornom obliku)
function token_dodaj($korisnik_id, $trajanje_sati)
{
    $token = bin2hex(random_bytes(32));

    db_izvrsi(
        'INSERT INTO api_tokeni (korisnik_id, token_hash, istjece) VALUES (?, ?, NOW() + INTERVAL ? HOUR)',
        [(int) $korisnik_id, hash('sha256', $token), (int) $trajanje_sati]
    );

    return $token;
}

// Token koji još vrijedi (id, korisnik_id, istjece) ili null
function token_vazeci($token)
{
    return db_jedan_redak(
        'SELECT id, korisnik_id, istjece FROM api_tokeni WHERE token_hash = ? AND istjece > NOW()',
        [hash('sha256', $token)]
    );
}

function token_obrisi($id)
{
    db_izvrsi('DELETE FROM api_tokeni WHERE id = ?', [(int) $id]);
}

// Istekli tokeni više ne služe ničemu
function tokeni_obrisi_istekle()
{
    db_izvrsi('DELETE FROM api_tokeni WHERE istjece <= NOW()');
}
