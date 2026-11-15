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
INSERT INTO vijesti (id, kategorija_id, grad_opcina_id, autor_id, naslov, sazetak, sadrzaj, status, istaknuto, objavljeno_u) VALUES
(1, 1, NULL, 1, 'Počinje nova turistička sezona izvan ljeta', 'Sve više gradova nudi programe i u jesen i zimu, a posjetitelji ih rado koriste.', '<p>Turističke zajednice diljem Hrvatske ove godine pripremile su bogatiji program za jesen i zimu.</p><p>Posebno su traženi izleti u prirodu i gastronomske ture.</p>', 'objavljeno', 1, '2026-11-14 09:00:00'),
(2, 2, 556, 2, 'Advent u Zagrebu otvara se krajem studenog', 'Klizalište, koncerti i kućice s hranom ponovno u središtu grada.', '<p>Advent u Zagrebu ove godine počinje 28. studenoga.</p><p>Program uključuje koncerte na otvorenom i klizalište na Trgu kralja Tomislava.</p>', 'objavljeno', 1, '2026-11-13 10:30:00'),
(3, 4, 474, 2, 'Nove brodske linije prema Braču i Šolti', 'Od prosinca više polazaka iz splitske luke tijekom vikenda.', '<p>Splitska luka od prosinca uvodi dodatne vikend polaske prema Supetru i Rogaču.</p>', 'objavljeno', 0, '2026-11-12 08:15:00'),
(4, 5, 436, 3, 'Zlatni rat zaštićen od erozije', 'Postavljene su prirodne prepreke koje čuvaju oblik poznate plaže.', '<p>Na plaži Zlatni rat postavljene su prirodne prepreke protiv erozije.</p>', 'objavljeno', 0, '2026-11-06 09:30:00'),
(5, 5, 364, 3, 'Uređena poučna staza na Srđu', 'Nova staza s info pločama vodi od gradskih zidina do vrha brda.', '<p>Poučna staza na Srđu dobila je nove info ploče o biljkama i povijesti utvrde Imperijal.</p>', 'objavljeno', 1, '2026-11-11 12:00:00'),
(6, 3, 474, 2, 'Nacrt: najava zimskog kupanja na Bačvicama', 'Tradicionalno novogodišnje kupanje i picigin.', '<p>Tekst u pripremi.</p>', 'nacrt', 0, NULL),
(7, 2, 364, 2, 'Zakazano: Festa svetog Vlaha 2027.', 'Program proslave Dana grada Dubrovnika.', '<p>Program će biti objavljen uskoro.</p>', 'objavljeno', 0, '2027-01-20 09:00:00');
