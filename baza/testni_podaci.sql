-- Mjestopis - testni podaci za demonstraciju
-- Svi podaci su izmišljeni. Svi testni računi imaju lozinku: Mjestopis123!

USE mjestopis;

SET NAMES utf8mb4;

INSERT INTO korisnici (id, uloga_id, ime, email, lozinka_hash, aktivan) VALUES
(1, 3, 'Ana Administrator', 'admin@mjestopis.test', '$2y$12$hhjGSyQei1smg42U8QyckuSt6EJ2nv.w.rjoqRAmaiuB.uLugREWi', 1),
(2, 2, 'Ivan Urednik', 'urednik1@mjestopis.test', '$2y$12$hhjGSyQei1smg42U8QyckuSt6EJ2nv.w.rjoqRAmaiuB.uLugREWi', 1),
(3, 2, 'Petra Urednica', 'urednik2@mjestopis.test', '$2y$12$hhjGSyQei1smg42U8QyckuSt6EJ2nv.w.rjoqRAmaiuB.uLugREWi', 1),
(4, 1, 'Marko Turist', 'korisnik@mjestopis.test', '$2y$12$hhjGSyQei1smg42U8QyckuSt6EJ2nv.w.rjoqRAmaiuB.uLugREWi', 1),
(5, 1, 'Lea Neaktivna', 'neaktivan@mjestopis.test', '$2y$12$hhjGSyQei1smg42U8QyckuSt6EJ2nv.w.rjoqRAmaiuB.uLugREWi', 0);

INSERT INTO kategorije (id, naziv) VALUES
(1, 'Turizam'),
(2, 'Kultura'),
(3, 'Sport'),
(4, 'Promet'),
(5, 'Priroda');

-- Vijesti: pet objavljenih, jedan nacrt i jedna zakazana za budućnost.
-- grad_opcina_id: 556 Zagreb, 474 Split, 436 Bol, 364 Dubrovnik, NULL cijela Hrvatska
INSERT INTO vijesti (id, kategorija_id, grad_opcina_id, autor_id, naslov, sazetak, sadrzaj, status, objavljeno_u) VALUES
(1, 1, NULL, 1, 'Počinje nova turistička sezona izvan ljeta', 'Sve više gradova nudi programe i u jesen i zimu, a posjetitelji ih rado koriste.', '<p>Turističke zajednice diljem Hrvatske ove godine pripremile su bogatiji program za jesen i zimu.</p><p>Posebno su traženi izleti u prirodu i gastronomske ture.</p>', 'objavljeno', '2026-11-14 09:00:00'),
(2, 2, 556, 2, 'Advent u Zagrebu otvara se krajem studenog', 'Klizalište, koncerti i kućice s hranom ponovno u središtu grada.', '<p>Advent u Zagrebu ove godine počinje 28. studenoga.</p><p>Program uključuje koncerte na otvorenom i klizalište na Trgu kralja Tomislava.</p>', 'objavljeno', '2026-11-13 10:30:00'),
(3, 4, 474, 2, 'Nove brodske linije prema Braču i Šolti', 'Od prosinca više polazaka iz splitske luke tijekom vikenda.', '<p>Splitska luka od prosinca uvodi dodatne vikend polaske prema Supetru i Rogaču.</p>', 'objavljeno', '2026-11-12 08:15:00'),
(4, 5, 436, 3, 'Zlatni rat zaštićen od erozije', 'Postavljene su prirodne prepreke koje čuvaju oblik poznate plaže.', '<p>Na plaži Zlatni rat postavljene su prirodne prepreke protiv erozije.</p>', 'objavljeno', '2026-11-06 09:30:00'),
(5, 5, 364, 3, 'Uređena poučna staza na Srđu', 'Nova staza s info pločama vodi od gradskih zidina do vrha brda.', '<p>Poučna staza na Srđu dobila je nove info ploče o biljkama i povijesti utvrde Imperijal.</p>', 'objavljeno', '2026-11-11 12:00:00'),
(6, 3, 474, 2, 'Nacrt: najava zimskog kupanja na Bačvicama', 'Tradicionalno novogodišnje kupanje i picigin.', '<p>Tekst u pripremi.</p>', 'nacrt', NULL),
(7, 2, 364, 2, 'Zakazano: Festa svetog Vlaha 2027.', 'Program proslave Dana grada Dubrovnika.', '<p>Program će biti objavljen uskoro.</p>', 'objavljeno', '2027-01-20 09:00:00');

INSERT INTO vrste_lokacija (id, naziv) VALUES
(1, 'Znamenitost'),
(2, 'Park'),
(3, 'Staza'),
(4, 'Plaža'),
(5, 'Vidikovac'),
(6, 'Muzej');

-- Lokacije: deset objavljenih (četiri istaknute), jedan nacrt i jedna povučena.
-- grad_opcina_id: 556 Zagreb, 474 Split, 436 Bol, 364 Dubrovnik, 116 Plitvička Jezera, 518 Rovinj, 516 Pula, 318 Zadar, 142 Varaždin
-- radno_vrijeme NULL znači uvijek otvoreno, a null za pojedini dan znači zatvoreno.
INSERT INTO lokacije (id, vrsta_id, grad_opcina_id, autor_id, naziv, opis, adresa, lat, lng, radno_vrijeme, status, istaknuto) VALUES
(1, 1, 364, 2, 'Gradske zidine', 'Zidine dugačke gotovo dva kilometra okružuju staru jezgru Dubrovnika. Šetnja po njima nudi pogled na krovove grada i otvoreno more.', 'Ulica Svetog Dominika', 42.641860, 18.110440, '{"pon": ["08:00", "18:00"], "uto": ["08:00", "18:00"], "sri": ["08:00", "18:00"], "cet": ["08:00", "18:00"], "pet": ["08:00", "18:00"], "sub": ["08:00", "18:00"], "ned": ["08:00", "18:00"], "napomena": "Ljeti do 19:30"}', 'objavljeno', 1),
(2, 5, 364, 3, 'Srđ', 'Brdo iznad Dubrovnika s utvrdom Imperijal. Do vrha vodi žičara ili poučna staza od gradskih zidina.', NULL, 42.649440, 18.114170, NULL, 'objavljeno', 0),
(3, 4, 436, 3, 'Zlatni rat', 'Šljunčana plaža koja mijenja oblik ovisno o vjetru i strujama. Od središta Bola do nje vodi šetnica uz more.', NULL, 43.255830, 16.633890, NULL, 'objavljeno', 1),
(4, 1, 474, 2, 'Dioklecijanova palača', 'Rimska palača s početka 4. stoljeća u kojoj se i danas živi. Podrumi, Peristil i katedrala svetog Duje dio su obilaska.', 'Peristil bb', 43.508130, 16.440280, NULL, 'objavljeno', 1),
(5, 2, 474, 2, 'Marjan', 'Park šuma na zapadu Splita sa stazama, vidikovcima i crkvicama. Pogodna za šetnju, trčanje i vožnju biciklom.', NULL, 43.509170, 16.410560, NULL, 'objavljeno', 0),
(6, 3, 116, 3, 'Staza oko Donjih jezera', 'Kružna staza drvenim mostovima uz Donja jezera do Velikog slapa. Obilazak traje oko tri sata.', 'Ulaz 1, Rastovača', 44.903330, 15.609440, '{"pon": ["08:00", "16:00"], "uto": ["08:00", "16:00"], "sri": ["08:00", "16:00"], "cet": ["08:00", "16:00"], "pet": ["08:00", "16:00"], "sub": ["08:00", "16:00"], "ned": ["08:00", "16:00"], "napomena": "Radno vrijeme se mijenja po sezoni"}', 'objavljeno', 1),
(7, 6, 556, 2, 'Muzej prekinutih veza', 'Muzej osobnih predmeta i priča o završenim vezama, smješten u baroknoj palači na Gornjem gradu.', 'Ćirilometodska 2', 45.815280, 15.973060, '{"pon": ["09:00", "21:00"], "uto": ["09:00", "21:00"], "sri": ["09:00", "21:00"], "cet": ["09:00", "21:00"], "pet": ["09:00", "21:00"], "sub": ["09:00", "21:00"], "ned": ["09:00", "21:00"]}', 'objavljeno', 0),
(8, 2, 556, 3, 'Maksimir', 'Najstariji javni park u Zagrebu s jezerima, šumom i zoološkim vrtom.', 'Maksimirski perivoj bb', 45.822220, 16.018330, NULL, 'objavljeno', 0),
(9, 1, 516, 2, 'Arena', 'Rimski amfiteatar iz 1. stoljeća, jedan od najbolje očuvanih na svijetu. Ljeti se u njoj održavaju koncerti i filmski festival.', 'Flavijevska ulica', 44.873060, 13.850280, '{"pon": ["09:00", "17:00"], "uto": ["09:00", "17:00"], "sri": ["09:00", "17:00"], "cet": ["09:00", "17:00"], "pet": ["09:00", "17:00"], "sub": ["09:00", "17:00"], "ned": null}', 'objavljeno', 0),
(10, 5, 318, 3, 'Morske orgulje', 'Kamene stepenice na rivi u kojima valovi sviraju. U blizini je instalacija Pozdrav Suncu, posebno lijepa u sumrak.', 'Obala kralja Petra Krešimira IV.', 44.116940, 15.219720, NULL, 'objavljeno', 0),
(11, 1, 518, 2, 'Crkva svete Eufemije', 'Barokna crkva na vrhu staroga grada. Tekst opisa još nije gotov.', 'Petra Stankovića', 45.083610, 13.629170, NULL, 'nacrt', 0),
(12, 2, 142, 3, 'Gradsko groblje', 'Groblje uređeno kao park s pažljivo oblikovanim živicama. Lokacija je povučena dok se opis ne provjeri.', 'Hallerova aleja', 46.306670, 16.328890, NULL, 'povuceno', 0);
