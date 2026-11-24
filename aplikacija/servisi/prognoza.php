<?php
// Prognoza vremena po gradu/općini s OpenMeteo API-ja (https://open-meteo.com)
// Dohvaća se 16 dana prognoze za koordinate sjedišta grada/općina i čuva u tablici predmemorija_prognoze 1 sat.
// Ako API ne odgovori ili vrati neispravne podatke, greška se zapisuje u log i prikazuje se zadnja uspješno dohvaćena prognoza. Ako ni nje nema, prognoze nema (stranica radi i bez nje).

const PROGNOZA_ADRESA = 'https://api.open-meteo.com/v1/forecast';
const PROGNOZA_BROJ_DANA = 16;
const PROGNOZA_TIMEOUT = 5;
const PROGNOZA_TRAJANJE = 3600;
const PROGNOZA_PONOVNI_POKUSAJ = 600;
const PROGNOZA_NEISPRAVAN_ODGOVOR = __DIR__ . '/../../testni-podaci/openmeteo/neispravno.json';

// Način rada iz konfiguracije (PROGNOZA_NACIN): 'stvarno' je pravi API, a 'greska' i 'neispravno' su testni načini
// za demonstraciju kvara servisa (servis ne odgovara, odgovor bez očekivanih polja). Bez konstante ili s nepoznatom vrijednošću: 'stvarno'.
function prognoza_nacin()
{
    $nacin = defined('PROGNOZA_NACIN') ? PROGNOZA_NACIN : 'stvarno';

    return in_array($nacin, ['stvarno', 'greska', 'neispravno'], true) ? $nacin : 'stvarno';
}

// Prognoza za grad/općinu (redak s id, naziv, lat, lng).
// Vraća ['dani' => [...], 'dohvaceno' => '2026-11-22 14:05:00', 'zastarjelo' => false] ili null.
// Svaki dan je ['datum', 'kod', 'najvisa', 'najniza', 'oborine', 'vjetar']; oborine (%) i vjetar (km/h) mogu biti null.
// Prošli dani se ne vraćaju.
function prognoza_grada($grad)
{
    $zapis = db_jedan_redak(
        'SELECT podaci, dohvaceno, istjece FROM predmemorija_prognoze WHERE grad_opcina_id = ?',
        [(int) $grad['id']]
    );

    // U testnom načinu svježa predmemorija se zaobilazi, inače bi se kvar vidio tek kad zapis istekne
    $testni_nacin = prognoza_nacin() !== 'stvarno';

    if ($zapis !== null && strtotime($zapis['istjece']) > time() && !$testni_nacin) {
        return prognoza_iz_zapisa($zapis);
    }

    $dani = prognoza_dohvati($grad);

    if ($dani !== null) {
        $sada = time();
        $zapis = [
            'podaci' => json_encode($dani, JSON_PRESERVE_ZERO_FRACTION),
            'dohvaceno' => date('Y-m-d H:i:s', $sada),
            'istjece' => date('Y-m-d H:i:s', $sada + PROGNOZA_TRAJANJE),
        ];
        db_izvrsi(
            'INSERT INTO predmemorija_prognoze (grad_opcina_id, podaci, dohvaceno, istjece) VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE podaci = VALUES(podaci), dohvaceno = VALUES(dohvaceno), istjece = VALUES(istjece)',
            [(int) $grad['id'], $zapis['podaci'], $zapis['dohvaceno'], $zapis['istjece']]
        );

        return prognoza_iz_zapisa($zapis);
    }

    if ($zapis === null) {
        return null;
    }

    // Testni kvar ne odgađa sljedeći pravi poziv, pa nakon povratka na 'stvarno' prognoza odmah radi
    if ($testni_nacin) {
        return prognoza_iz_zapisa($zapis, true);
    }

    // API ne radi: stara prognoza ostaje, a novi pokušaj tek za 10 minuta da svaka stranica ne čeka timeout.
    db_izvrsi(
        'UPDATE predmemorija_prognoze SET istjece = ? WHERE grad_opcina_id = ?',
        [date('Y-m-d H:i:s', time() + PROGNOZA_PONOVNI_POKUSAJ), (int) $grad['id']]
    );

    return prognoza_iz_zapisa($zapis, true);
}

// Prognoza grada/općine samo za dane od $od do $do (Y-m-d), npr. za lokaciju ili događanje.
// Vraća isto što i prognoza_grada() ili null ako za te dane nema prognoze.
function prognoza_razdoblja($grad_opcina_id, $od, $do)
{
    $prognoza = prognoza_grada(grad_opcina_po_id($grad_opcina_id));

    if ($prognoza === null) {
        return null;
    }

    $prognoza['dani'] = array_values(array_filter(
        $prognoza['dani'],
        fn($dan) => $dan['datum'] >= $od && $dan['datum'] <= $do
    ));

    return count($prognoza['dani']) > 0 ? $prognoza : null;
}

// Prognoza iz retka predmemorije, bez prošlih dana.
// Zastarjela je ako je dohvaćena prije više od sat vremena ili ako dohvaćanje nove upravo nije uspjelo ($neuspjelo).
function prognoza_iz_zapisa($zapis, $neuspjelo = false)
{
    $dani = json_decode($zapis['podaci'], true);

    if (!is_array($dani)) {
        return null;
    }

    $danas = date('Y-m-d');
    $dani = array_values(array_filter($dani, fn($dan) => $dan['datum'] >= $danas));

    if (count($dani) === 0) {
        return null;
    }

    return [
        'dani' => $dani,
        'dohvaceno' => $zapis['dohvaceno'],
        'zastarjelo' => $neuspjelo || strtotime($zapis['dohvaceno']) + PROGNOZA_TRAJANJE < time(),
    ];
}

// Zahtjev prema OpenMeteo API-ju. Vraća obrađene dane ili null (razlog se zapisuje u log).
function prognoza_dohvati($grad)
{
    $adresa = PROGNOZA_ADRESA . '?' . http_build_query([
        'latitude' => $grad['lat'],
        'longitude' => $grad['lng'],
        'daily' => 'weather_code,temperature_2m_max,temperature_2m_min,precipitation_probability_max,wind_speed_10m_max',
        'timezone' => 'Europe/Zagreb',
        'forecast_days' => PROGNOZA_BROJ_DANA,
    ]);
    $nacin = prognoza_nacin();
    $opis = ($nacin !== 'stvarno' ? '[TESTNI NAČIN ' . $nacin . '] ' : '') . 'Prognoza za ' . $grad['naziv'] . ' (id ' . $grad['id'] . ')';

    if ($nacin === 'greska') {
        // Glumi se servis koji ne odgovara (kao da je istekao timeout), bez stvarnog zahtjeva
        error_log($opis . ': zahtjev nije uspio (servis ne odgovara)');
        return null;
    }

    if ($nacin === 'neispravno') {
        // Umjesto API-ja čita se testni odgovor bez očekivanih polja, a dalje ide kroz iste provjere kao pravi odgovor
        $odgovor = file_get_contents(PROGNOZA_NEISPRAVAN_ODGOVOR);
    } else {
        $zahtjev = curl_init($adresa);
        curl_setopt_array($zahtjev, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => PROGNOZA_TIMEOUT,
            CURLOPT_TIMEOUT => PROGNOZA_TIMEOUT,
        ]);
        $odgovor = curl_exec($zahtjev);

        if ($odgovor === false) {
            error_log($opis . ': zahtjev nije uspio (' . curl_error($zahtjev) . ')');
            return null;
        }

        $status = curl_getinfo($zahtjev, CURLINFO_RESPONSE_CODE);
        if ($status !== 200) {
            error_log($opis . ': API je vratio HTTP ' . $status);
            return null;
        }
    }

    $podaci = json_decode($odgovor, true);
    if (!is_array($podaci)) {
        error_log($opis . ': odgovor nije ispravan JSON (' . json_last_error_msg() . ')');
        return null;
    }

    $dani = prognoza_obradi($podaci, $razlog);
    if ($dani === null) {
        error_log($opis . ': odgovor nema očekivanu strukturu (' . $razlog . ')');
        return null;
    }

    return $dani;
}

// Provjera strukture odgovora i pretvaranje u popis dana.
// Polje daily ima niz za svaku veličinu, a svi nizovi moraju biti iste duljine kao niz datuma.
// Ako odgovor nije ispravan, vraća null, a u $razlog upisuje što ne valja (za log).
function prognoza_obradi($podaci, &$razlog = null)
{
    $dnevno = $podaci['daily'] ?? null;

    if (!is_array($dnevno)) {
        $razlog = 'nedostaje polje daily';
        return null;
    }

    if (!isset($dnevno['time']) || !is_array($dnevno['time']) || !array_is_list($dnevno['time']) || count($dnevno['time']) === 0) {
        $razlog = 'nedostaje niz datuma daily.time';
        return null;
    }

    // Svi problemi s poljima navode se odjednom, npr. „nedostaje daily.temperature_2m_min; daily.weather_code ima 2 vrijednosti umjesto 3”
    $broj_dana = count($dnevno['time']);
    $problemi = [];
    foreach (['weather_code', 'temperature_2m_max', 'temperature_2m_min', 'precipitation_probability_max', 'wind_speed_10m_max'] as $polje) {
        if (!isset($dnevno[$polje])) {
            $problemi[] = 'nedostaje daily.' . $polje;
        } elseif (!is_array($dnevno[$polje]) || !array_is_list($dnevno[$polje])) {
            $problemi[] = 'daily.' . $polje . ' nije niz';
        } elseif (count($dnevno[$polje]) !== $broj_dana) {
            $problemi[] = 'daily.' . $polje . ' ima ' . count($dnevno[$polje]) . ' vrijednosti umjesto ' . $broj_dana;
        }
    }

    if (count($problemi) > 0) {
        $razlog = implode('; ', $problemi);
        return null;
    }

    $dani = [];
    foreach ($dnevno['time'] as $i => $datum) {
        $kod = $dnevno['weather_code'][$i];
        $najvisa = $dnevno['temperature_2m_max'][$i];
        $najniza = $dnevno['temperature_2m_min'][$i];
        $oborine = $dnevno['precipitation_probability_max'][$i];
        $vjetar = $dnevno['wind_speed_10m_max'][$i];

        // Datum, vrijeme i temperature su obavezni, a oborine i vjetar za daleke dane znaju biti null.
        // Navodi se prva neispravna vrijednost, npr. „daily.temperature_2m_max[3] nije broj”.
        $razlog = match (true) {
            !is_string($datum) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $datum) => 'daily.time[' . $i . '] nije datum',
            !is_int($kod) => 'daily.weather_code[' . $i . '] nije cijeli broj',
            !prognoza_je_broj($najvisa) => 'daily.temperature_2m_max[' . $i . '] nije broj',
            !prognoza_je_broj($najniza) => 'daily.temperature_2m_min[' . $i . '] nije broj',
            $oborine !== null && !prognoza_je_broj($oborine) => 'daily.precipitation_probability_max[' . $i . '] nije broj',
            $vjetar !== null && !prognoza_je_broj($vjetar) => 'daily.wind_speed_10m_max[' . $i . '] nije broj',
            default => null,
        };

        if ($razlog !== null) {
            return null;
        }

        $dani[] = [
            'datum' => $datum,
            'kod' => $kod,
            'najvisa' => (float) $najvisa,
            'najniza' => (float) $najniza,
            'oborine' => $oborine === null ? null : (int) round($oborine),
            'vjetar' => $vjetar === null ? null : (float) $vjetar,
        ];
    }

    return $dani;
}

function prognoza_je_broj($vrijednost)
{
    return is_int($vrijednost) || is_float($vrijednost);
}

// Opis vremena prema WMO kodu iz OpenMeteo odgovora
function opis_vremena($kod)
{
    $opisi = [
        0 => 'Vedro',
        1 => 'Pretežno vedro',
        2 => 'Djelomično oblačno',
        3 => 'Oblačno',
        45 => 'Magla',
        48 => 'Magla s injem',
        51 => 'Slaba rosulja',
        53 => 'Rosulja',
        55 => 'Jaka rosulja',
        56 => 'Ledena rosulja',
        57 => 'Jaka ledena rosulja',
        61 => 'Slaba kiša',
        63 => 'Kiša',
        65 => 'Jaka kiša',
        66 => 'Ledena kiša',
        67 => 'Jaka ledena kiša',
        71 => 'Slab snijeg',
        73 => 'Snijeg',
        75 => 'Jak snijeg',
        77 => 'Snježna zrnca',
        80 => 'Slabi pljuskovi',
        81 => 'Pljuskovi',
        82 => 'Jaki pljuskovi',
        85 => 'Slabi snježni pljuskovi',
        86 => 'Snježni pljuskovi',
        95 => 'Grmljavina',
        96 => 'Grmljavina s tučom',
        99 => 'Grmljavina s jakom tučom',
    ];

    return $opisi[$kod] ?? 'Nepoznato';
}

// Ikona vremena prema WMO kodu, kao modifikator klase ikona-vremena (npr. „kisa”).
// Koja Lucide ikona odgovara kojem modifikatoru, zapisano je u $weather-icons u scss/_kartice.scss. Nepoznat kod dobiva oblak.
function ikona_vremena($kod)
{
    $ikone = [
        0 => 'sunce',
        1 => 'sunce',
        2 => 'djelomicno-oblacno',
        3 => 'oblacno',
        45 => 'magla',
        48 => 'magla',
        51 => 'rosulja',
        53 => 'rosulja',
        55 => 'rosulja',
        56 => 'rosulja',
        57 => 'rosulja',
        61 => 'kisa',
        63 => 'kisa',
        65 => 'jaka-kisa',
        66 => 'kisa',
        67 => 'jaka-kisa',
        71 => 'snijeg',
        73 => 'snijeg',
        75 => 'snijeg',
        77 => 'snjezna-zrnca',
        80 => 'pljuskovi',
        81 => 'jaka-kisa',
        82 => 'jaka-kisa',
        85 => 'snjezni-pljuskovi',
        86 => 'snijeg',
        95 => 'grmljavina',
        96 => 'tuca',
        99 => 'tuca',
    ];

    return $ikone[$kod] ?? 'oblak';
}
