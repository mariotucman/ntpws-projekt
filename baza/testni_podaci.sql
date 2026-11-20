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
(2, 'Sakralni objekt'),
(3, 'Utvrda'),
(4, 'Muzej i galerija'),
(5, 'Plaža'),
(6, 'Park'),
(7, 'Zaštićena priroda'),
(8, 'Staza'),
(9, 'Vidikovac'),
(10, 'Slap i jezero'),
(11, 'Špilja');

-- Lokacije: 42 objavljene (deset istaknutih) i dvije skrivene, više od pola u kontinentalnoj Hrvatskoj.
-- grad_opcina_id: 556 Zagreb, 27 Samobor, 47 Krapina, 37 Desinić, 49 Kumrovec, 52 Marija Bistrica, 82 Sisak,
-- 104 Karlovac, 105 Ogulin, 107 Slunj, 97 Rakovica, 116 Plitvička Jezera, 142 Varaždin, 120 Bednja, 532 Čakovec,
-- 345 Osijek, 323 Bilje, 332 Đakovo, 257 Velika, 433 Vukovar, 413 Ilok, 227 Skrad,
-- 515 Poreč, 516 Pula, 518 Rovinj, 318 Zadar, 309 Sali, 398 Skradin, 474 Split, 436 Bol, 453 Makarska, 364 Dubrovnik
-- radno_vrijeme NULL znači uvijek otvoreno, a null za pojedini dan znači zatvoreno.
INSERT INTO lokacije (id, vrsta_id, grad_opcina_id, autor_id, naziv, opis, adresa, lat, lng, radno_vrijeme, objavljeno, istaknuto) VALUES
(1, 1, 364, 2, 'Gradske zidine', 'Zidine dugačke gotovo dva kilometra okružuju staru jezgru Dubrovnika. Šetnja po njima nudi pogled na krovove grada i otvoreno more.', 'Ulica Svetog Dominika', 42.641860, 18.110440, '{"pon": ["08:00", "18:00"], "uto": ["08:00", "18:00"], "sri": ["08:00", "18:00"], "cet": ["08:00", "18:00"], "pet": ["08:00", "18:00"], "sub": ["08:00", "18:00"], "ned": ["08:00", "18:00"], "napomena": "Ljeti do 19:30"}', 1, 1),
(2, 9, 364, 3, 'Srđ', 'Brdo iznad Dubrovnika s utvrdom Imperijal. Do vrha vodi žičara ili poučna staza od gradskih zidina.', NULL, 42.649440, 18.114170, NULL, 1, 0),
(3, 5, 436, 3, 'Zlatni rat', 'Šljunčana plaža koja mijenja oblik ovisno o vjetru i strujama. Od središta Bola do nje vodi šetnica uz more.', NULL, 43.255830, 16.633890, NULL, 1, 1),
(4, 1, 474, 2, 'Dioklecijanova palača', 'Rimska palača s početka 4. stoljeća u kojoj se i danas živi. Podrumi, Peristil i katedrala svetog Duje dio su obilaska.', 'Peristil bb', 43.508130, 16.440280, NULL, 1, 1),
(5, 6, 474, 2, 'Marjan', 'Park šuma na zapadu Splita sa stazama, vidikovcima i crkvicama. Pogodna za šetnju, trčanje i vožnju biciklom.', NULL, 43.509170, 16.410560, NULL, 1, 0),
(6, 8, 116, 3, 'Staza oko Donjih jezera', 'Kružna staza drvenim mostovima uz Donja jezera do Velikog slapa. Obilazak traje oko tri sata.', 'Ulaz 1, Rastovača', 44.903330, 15.609440, '{"pon": ["08:00", "16:00"], "uto": ["08:00", "16:00"], "sri": ["08:00", "16:00"], "cet": ["08:00", "16:00"], "pet": ["08:00", "16:00"], "sub": ["08:00", "16:00"], "ned": ["08:00", "16:00"], "napomena": "Radno vrijeme se mijenja po sezoni"}', 1, 1),
(7, 4, 556, 2, 'Muzej prekinutih veza', 'Muzej osobnih predmeta i priča o završenim vezama, smješten u baroknoj palači na Gornjem gradu.', 'Ćirilometodska 2', 45.815280, 15.973060, '{"pon": ["09:00", "21:00"], "uto": ["09:00", "21:00"], "sri": ["09:00", "21:00"], "cet": ["09:00", "21:00"], "pet": ["09:00", "21:00"], "sub": ["09:00", "21:00"], "ned": ["09:00", "21:00"]}', 1, 0),
(8, 6, 556, 3, 'Maksimir', 'Najstariji javni park u Zagrebu s jezerima, šumom i zoološkim vrtom.', 'Maksimirski perivoj bb', 45.822220, 16.018330, NULL, 1, 0),
(9, 1, 516, 2, 'Arena', 'Rimski amfiteatar iz 1. stoljeća, jedan od najbolje očuvanih na svijetu. Ljeti se u njoj održavaju koncerti i filmski festival.', 'Flavijevska ulica', 44.873060, 13.850280, '{"pon": ["09:00", "17:00"], "uto": ["09:00", "17:00"], "sri": ["09:00", "17:00"], "cet": ["09:00", "17:00"], "pet": ["09:00", "17:00"], "sub": ["09:00", "17:00"], "ned": null}', 1, 0),
(10, 1, 318, 3, 'Morske orgulje', 'Kamene stepenice na rivi u kojima valovi sviraju. U blizini je instalacija Pozdrav Suncu, posebno lijepa u sumrak.', 'Obala kralja Petra Krešimira IV.', 44.116940, 15.219720, NULL, 1, 0),
(11, 2, 518, 2, 'Crkva svete Eufemije', 'Barokna crkva na vrhu staroga grada. Tekst opisa još nije gotov.', 'Petra Stankovića', 45.083610, 13.629170, NULL, 0, 0),
(12, 6, 142, 3, 'Gradsko groblje', 'Groblje uređeno kao park s pažljivo oblikovanim živicama. Lokacija je skrivena dok se opis ne provjeri.', 'Hallerova aleja', 46.306670, 16.328890, NULL, 0, 0),
(13, 3, 120, 2, 'Dvorac Trakošćan', 'Dvorac na brežuljku iznad umjetnog jezera, okružen šumom. U dvorcu je muzej s namještajem, oružjem i portretima obitelji Drašković, a oko jezera vodi staza od pet kilometara.', 'Trakošćan 1', 46.258330, 15.942500, '{"pon": ["09:00", "17:00"], "uto": ["09:00", "17:00"], "sri": ["09:00", "17:00"], "cet": ["09:00", "17:00"], "pet": ["09:00", "17:00"], "sub": ["09:00", "17:00"], "ned": ["09:00", "17:00"], "napomena": "Od travnja do listopada do 18 h"}', 1, 1),
(14, 3, 142, 3, 'Stari grad Varaždin', 'Utvrda okružena zemljanim bedemima i opkopom, danas Gradski muzej s povijesnom zbirkom.', 'Strossmayerovo šetalište 3', 46.310560, 16.333610, '{"pon": null, "uto": ["09:00", "17:00"], "sri": ["09:00", "17:00"], "cet": ["09:00", "17:00"], "pet": ["09:00", "17:00"], "sub": ["09:00", "13:00"], "ned": ["09:00", "13:00"]}', 1, 0),
(15, 1, 345, 2, 'Tvrđa', 'Barokni grad unutar zidina iz 18. stoljeća, s Trgom Svetog Trojstva, muzejima i kafićima. Najbolje očuvana barokna urbana cjelina u Hrvatskoj.', 'Trg Svetog Trojstva', 45.560830, 18.695830, NULL, 1, 0),
(16, 7, 323, 3, 'Park prirode Kopački rit', 'Poplavno područje između Dunava i Drave, jedno od najvažnijih staništa ptica u Europi. Obilazi se brodom, drvenim stazama i biciklom.', 'Mali Sakadaš 1', 45.603060, 18.860280, '{"pon": ["09:00", "17:00"], "uto": ["09:00", "17:00"], "sri": ["09:00", "17:00"], "cet": ["09:00", "17:00"], "pet": ["09:00", "17:00"], "sub": ["09:00", "17:00"], "ned": ["09:00", "17:00"], "napomena": "Vožnje brodom samo od travnja do listopada"}', 1, 1),
(17, 2, 332, 2, 'Katedrala svetog Petra i Pavla', 'Neoromanička katedrala od crvene opeke s tornjevima visokim 84 metra, simbol Đakova. Unutrašnjost je oslikana freskama.', 'Strossmayerov trg 6', 45.309170, 18.410000, NULL, 1, 0),
(18, 4, 433, 3, 'Muzej vučedolske kulture', 'Muzej uz arheološko nalazište Vučedol na obali Dunava, posvećen kulturi iz bakrenog doba. Najpoznatiji nalaz je Vučedolska golubica.', 'Vučedol 252', 45.348610, 19.041110, '{"pon": null, "uto": ["10:00", "18:00"], "sri": ["10:00", "18:00"], "cet": ["10:00", "18:00"], "pet": ["10:00", "18:00"], "sub": ["10:00", "18:00"], "ned": ["10:00", "18:00"]}', 1, 0),
(19, 9, 433, 2, 'Vodotoranj', 'Simbol stradanja Vukovara u Domovinskom ratu, obnovljen kao spomen-mjesto. S vrha se pruža pogled na grad, Dunav i Vuku.', 'Ulica Josipa Jurja Strossmayera', 45.338610, 19.007220, '{"pon": ["09:00", "17:00"], "uto": ["09:00", "17:00"], "sri": ["09:00", "17:00"], "cet": ["09:00", "17:00"], "pet": ["09:00", "17:00"], "sub": ["09:00", "17:00"], "ned": ["09:00", "17:00"]}', 1, 0),
(20, 10, 107, 3, 'Rastoke', 'Staro naselje mlinica na mjestu gdje se Slunjčica slapovima ulijeva u Koranu. Kuće su izgrađene na sedrenim stijenama između slapova.', 'Rastoke 25b', 45.117220, 15.586110, NULL, 1, 1),
(21, 11, 97, 2, 'Špilje Barać', 'Špiljski sustav s dvoranama ukrašenim sigama i kolonijom šišmiša. Gornja špilja uređena je za obilazak s vodičem.', 'Nova Kršlja bb', 44.965000, 15.625000, '{"pon": ["10:00", "16:00"], "uto": ["10:00", "16:00"], "sri": ["10:00", "16:00"], "cet": ["10:00", "16:00"], "pet": ["10:00", "16:00"], "sub": ["10:00", "16:00"], "ned": ["10:00", "16:00"], "napomena": "Zimi samo uz najavu"}', 1, 0),
(22, 3, 104, 3, 'Stari grad Dubovac', 'Srednjovjekovna utvrda na brežuljku iznad Karlovca. Iz kule se vidi cijeli grad i ušća četiriju rijeka.', 'Zamršje 1', 45.493060, 15.541110, '{"pon": ["10:00", "18:00"], "uto": ["10:00", "18:00"], "sri": ["10:00", "18:00"], "cet": ["10:00", "18:00"], "pet": ["10:00", "18:00"], "sub": ["10:00", "18:00"], "ned": ["10:00", "18:00"]}', 1, 0),
(23, 4, 104, 2, 'Aquatika', 'Slatkovodni akvarij s ribama iz hrvatskih rijeka i jezera, smješten uz Koranu.', 'Branka Čavlovića Čavleka 1a', 45.494170, 15.566110, '{"pon": ["10:00", "20:00"], "uto": ["10:00", "20:00"], "sri": ["10:00", "20:00"], "cet": ["10:00", "20:00"], "pet": ["10:00", "20:00"], "sub": ["10:00", "20:00"], "ned": ["10:00", "20:00"]}', 1, 0),
(24, 4, 47, 3, 'Muzej krapinskih neandertalaca', 'Muzej uz nalazište Hušnjakovo, gdje su pronađeni ostaci neandertalaca stari oko 130 000 godina.', 'Šetalište Vilibalda Sluge bb', 46.161110, 15.867220, '{"pon": null, "uto": ["09:00", "18:00"], "sri": ["09:00", "18:00"], "cet": ["09:00", "18:00"], "pet": ["09:00", "18:00"], "sub": ["09:00", "18:00"], "ned": ["09:00", "18:00"]}', 1, 1),
(25, 3, 37, 2, 'Dvor Veliki Tabor', 'Utvrda s peterokutnom jezgrom i polukružnim kulama na vrhu brda Hum Košnički, jedna od najbolje očuvanih u Hrvatskom zagorju.', 'Košnički Hum 1', 46.136110, 15.782220, '{"pon": null, "uto": ["09:00", "17:00"], "sri": ["09:00", "17:00"], "cet": ["09:00", "17:00"], "pet": ["09:00", "17:00"], "sub": ["09:00", "17:00"], "ned": ["09:00", "17:00"]}', 1, 0),
(26, 2, 52, 3, 'Svetište Majke Božje Bistričke', 'Najveće marijansko svetište u Hrvatskoj, s crkvom, kalvarijom na brdu iznad nje i velikim prostorom za hodočasnike.', 'Trg pape Ivana Pavla II. 32', 45.981110, 16.108060, NULL, 1, 0),
(27, 2, 556, 2, 'Zagrebačka katedrala', 'Katedrala Uznesenja Blažene Djevice Marije na Kaptolu, najviša građevina u Hrvatskoj. Neogotički izgled dobila je nakon potresa 1880.', 'Kaptol 31', 45.814440, 15.979720, NULL, 1, 1),
(28, 7, 556, 3, 'Park prirode Medvednica', 'Planina iznad Zagreba s vrhom Sljeme, šumama, planinarskim domovima i rudnikom Zrinski. Do vrha vode brojne staze i žičara.', 'Sljemenska cesta', 45.900000, 15.948060, NULL, 1, 0),
(29, 10, 556, 2, 'Jarun', 'Jezero na jugozapadu Zagreba s veslačkom stazom, šljunčanim kupalištima, biciklističkom stazom oko jezera i ljetnim festivalima.', 'Jarunska cesta', 45.783060, 15.924170, NULL, 1, 0),
(30, 8, 27, 3, 'Staza na Japetić', 'Planinarska staza od Šoićeve kuće do vidikovca na vrhu Japetića, najvišeg vrha Samoborskog gorja. Uspon traje oko sat i pol.', 'Šoićeva kuća', 45.744170, 15.645000, NULL, 1, 0),
(31, 7, 257, 2, 'Park prirode Papuk', 'Prvi hrvatski geopark, sa šumama bukve, slapom Skakavac, jezerom Jankovac i stijenama vulkanskog podrijetla.', 'Stjepana Radića 46', 45.498060, 17.660000, NULL, 1, 0),
(32, 7, 82, 3, 'Park prirode Lonjsko polje', 'Poplavne livade uz Savu sa selima drvenih kuća i gnijezdima roda na krovovima. Čigoć je poznat kao europsko selo roda.', 'Čigoć 26', 45.415000, 16.630000, '{"pon": ["08:00", "16:00"], "uto": ["08:00", "16:00"], "sri": ["08:00", "16:00"], "cet": ["08:00", "16:00"], "pet": ["08:00", "16:00"], "sub": ["09:00", "16:00"], "ned": ["09:00", "16:00"]}', 1, 0),
(33, 8, 105, 2, 'Staza na Klek', 'Planinarska staza do vrha Kleka, stijene koja se diže iznad Ogulina i prema legendi je okupljalište vještica.', 'Bjelsko', 45.266940, 15.145830, NULL, 1, 0),
(34, 10, 227, 3, 'Zeleni vir', 'Slap visok sedamdesetak metara koji pada u špilju iznad zelenog jezera. Iz njega počinje kanjon Vražji prolaz.', 'Zeleni vir bb', 45.436110, 14.896110, NULL, 1, 0),
(35, 2, 515, 2, 'Eufrazijeva bazilika', 'Ranokršćanski kompleks iz 6. stoljeća sa zlatnim mozaicima u apsidi, pod zaštitom UNESCO-a.', 'Eufrazijeva 22', 45.229170, 13.593610, '{"pon": ["09:00", "18:00"], "uto": ["09:00", "18:00"], "sri": ["09:00", "18:00"], "cet": ["09:00", "18:00"], "pet": ["09:00", "18:00"], "sub": ["09:00", "18:00"], "ned": ["13:00", "18:00"]}', 1, 0),
(36, 11, 515, 3, 'Jama Baredine', 'Jama s pet dvorana bogatih sigama i podzemnim jezerima, u kojoj živi čovječja ribica. Obilazak s vodičem traje oko 40 minuta.', 'Nova Vas, Gedići 55', 45.266110, 13.649170, '{"pon": ["10:00", "17:00"], "uto": ["10:00", "17:00"], "sri": ["10:00", "17:00"], "cet": ["10:00", "17:00"], "pet": ["10:00", "17:00"], "sub": ["10:00", "17:00"], "ned": ["10:00", "17:00"], "napomena": "Zatvoreno od studenog do ožujka"}', 1, 0),
(37, 7, 398, 2, 'Nacionalni park Krka', 'Rijeka Krka sa sedam sedrenih slapova, od kojih je najpoznatiji Skradinski buk. Od Skradina do slapova vozi brod.', 'Trg Ivana Pavla II. 5', 43.804170, 15.963890, '{"pon": ["08:00", "18:00"], "uto": ["08:00", "18:00"], "sri": ["08:00", "18:00"], "cet": ["08:00", "18:00"], "pet": ["08:00", "18:00"], "sub": ["08:00", "18:00"], "ned": ["08:00", "18:00"], "napomena": "Ljeti do 20 h"}', 1, 1),
(38, 5, 309, 3, 'Sakarun', 'Pješčana plaža na sjeverozapadu Dugog otoka s plitkim tirkiznim morem i borovom šumom uz obalu.', NULL, 44.142000, 14.859000, NULL, 1, 0),
(39, 9, 453, 2, 'Skywalk Biokovo', 'Staklena platforma na rubu litice Biokova, gotovo 1200 metara iznad mora, s pogledom na Makarsku rivijeru i otoke.', 'Biokovska cesta', 43.325000, 17.062000, '{"pon": ["08:00", "20:00"], "uto": ["08:00", "20:00"], "sri": ["08:00", "20:00"], "cet": ["08:00", "20:00"], "pet": ["08:00", "20:00"], "sub": ["08:00", "20:00"], "ned": ["08:00", "20:00"], "napomena": "Zimi se cesta zatvara zbog snijega"}', 1, 0),
(40, 5, 474, 3, 'Bačvice', 'Pješčana gradska plaža blizu središta Splita, poznata po piciginu i plitkom moru.', 'Šetalište Petra Preradovića', 43.502220, 16.448060, NULL, 1, 0),
(41, 4, 49, 2, 'Staro selo Kumrovec', 'Muzej na otvorenom: zagorsko selo iz 19. stoljeća s kućama, radionicama i prikazima starih zanata.', 'Josipa Broza 19', 46.077500, 15.678060, '{"pon": ["09:00", "16:00"], "uto": ["09:00", "16:00"], "sri": ["09:00", "16:00"], "cet": ["09:00", "16:00"], "pet": ["09:00", "16:00"], "sub": ["09:00", "16:00"], "ned": ["09:00", "16:00"], "napomena": "Ljeti do 19 h"}', 1, 0),
(42, 3, 532, 3, 'Stari grad Zrinskih', 'Utvrda obitelji Zrinski u parku u središtu Čakovca, danas Muzej Međimurja.', 'Trg Republike 5', 46.386940, 16.434170, '{"pon": null, "uto": ["10:00", "18:00"], "sri": ["10:00", "18:00"], "cet": ["10:00", "18:00"], "pet": ["10:00", "18:00"], "sub": ["10:00", "13:00"], "ned": null}', 1, 0),
(43, 3, 413, 2, 'Dvorac Odescalchi', 'Dvorac na mjestu srednjovjekovne utvrde iznad Dunava, u najistočnijem hrvatskom gradu. U dvorcu je Muzej grada Iloka, a ispod njega stari podrumi vina.', 'Šetalište oca Mladena Barbarića 5', 45.222500, 19.376110, '{"pon": null, "uto": ["10:00", "18:00"], "sri": ["10:00", "18:00"], "cet": ["10:00", "18:00"], "pet": ["10:00", "18:00"], "sub": ["10:00", "18:00"], "ned": ["10:00", "18:00"]}', 1, 0),
(44, 8, 345, 3, 'Šetnica uz Dravu', 'Šetnica i biciklistička staza uz Dravu od Tvrđe do središta Osijeka, s pješačkim mostom preko rijeke prema Copacabani.', 'Šetalište kardinala Franje Šepera', 45.562220, 18.680000, NULL, 1, 0);
