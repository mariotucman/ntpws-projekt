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
