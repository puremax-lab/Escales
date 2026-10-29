-- Escales: demo data. Every demo account uses the password "password".

INSERT INTO countries (id, name, code, nationality) VALUES
(1, 'Allemagne', 'DE', 'Allemand(e)'),
(2, 'Autriche', 'AT', 'Autrichien(ne)'),
(3, 'Belgique', 'BE', 'Belge'),
(4, 'Cameroun', 'CM', 'Camerounais(e)'),
(5, 'Espagne', 'ES', 'Espagnol(e)'),
(6, 'France', 'FR', 'Français(e)'),
(7, 'Italie', 'IT', 'Italien(ne)'),
(8, 'Pays-Bas', 'NL', 'Néerlandais(e)'),
(9, 'Portugal', 'PT', 'Portugais(e)'),
(10, 'Royaume-Uni', 'GB', 'Britannique');

INSERT INTO cities (id, country_id, name, is_capital) VALUES
(1, 1, 'Berlin', 1), (2, 1, 'Munich', 0), (3, 1, 'Hambourg', 0),
(4, 2, 'Vienne', 1), (5, 2, 'Salzbourg', 0),
(6, 3, 'Bruxelles', 1), (7, 3, 'Anvers', 0), (8, 3, 'Gand', 0), (9, 3, 'Bruges', 0), (10, 3, 'Liège', 0), (11, 3, 'Namur', 0),
(12, 4, 'Yaoundé', 1), (13, 4, 'Douala', 0), (14, 4, 'Kribi', 0),
(15, 5, 'Madrid', 1), (16, 5, 'Barcelone', 0), (17, 5, 'Séville', 0), (18, 5, 'Valence', 0),
(19, 6, 'Paris', 1), (20, 6, 'Lyon', 0), (21, 6, 'Marseille', 0), (22, 6, 'Bordeaux', 0), (23, 6, 'Lille', 0),
(24, 7, 'Rome', 1), (25, 7, 'Florence', 0), (26, 7, 'Venise', 0), (27, 7, 'Naples', 0),
(28, 8, 'Amsterdam', 1), (29, 8, 'Rotterdam', 0), (30, 8, 'Utrecht', 0),
(31, 9, 'Lisbonne', 1), (32, 9, 'Porto', 0),
(33, 10, 'Londres', 1), (34, 10, 'Édimbourg', 0), (35, 10, 'Manchester', 0);

INSERT INTO users (id, pseudo, first_name, last_name, password_hash, birth_date, city_id, created_at) VALUES
(1, 'camille', 'Camille', 'Laurent', '$2y$10$RUOkoD7PoQBI7wstHdzeHe416La50xNJJ9AqLpTlQQ.j5RRH5OOpK', '1996-04-12', 6, '2024-09-02 10:00:00'),
(2, 'malik', 'Malik', 'Ekotto', '$2y$10$RUOkoD7PoQBI7wstHdzeHe416La50xNJJ9AqLpTlQQ.j5RRH5OOpK', '1993-09-03', 13, '2024-11-15 18:30:00'),
(3, 'lena', 'Léna', 'Martins', '$2y$10$RUOkoD7PoQBI7wstHdzeHe416La50xNJJ9AqLpTlQQ.j5RRH5OOpK', '1999-01-25', 10, '2025-02-20 09:15:00');

INSERT INTO visits (user_id, city_id, visited_on, rating, note, created_at) VALUES
(1, 24, '2024-10-03', 5, 'Trois jours ne suffisent pas. Le Colisée tôt le matin, sans la foule.', '2024-10-08 20:00:00'),
(2, 12, '2024-12-20', 3, 'Beaucoup de circulation, mais le marché central vaut le détour.', '2024-12-28 11:00:00'),
(1, 28, '2025-02-21', 4, 'Visite du Rijksmuseum et balade à vélo le long des canaux.', '2025-02-24 19:00:00'),
(3, 24, '2025-03-22', 4, 'Le Vatican mérite une journée entière.', '2025-03-26 21:00:00'),
(2, 31, '2025-04-10', 5, 'Ville lumineuse, on y mange très bien pour pas cher.', '2025-04-15 12:00:00'),
(1, 31, '2025-05-14', 5, 'Les tramways jaunes, les pastéis de nata et la vue depuis le miradouro da Graça.', '2025-05-20 18:00:00'),
(1, 32, '2025-05-18', 4, 'Croisière sur le Douro au coucher du soleil, à refaire.', '2025-05-20 18:10:00'),
(2, 19, '2025-06-02', 4, 'Montmartre au lever du jour et un pique-nique au Champ-de-Mars.', '2025-06-09 22:00:00'),
(2, 6, '2025-06-06', 4, 'La Grand-Place illuminée le soir, et des frites à chaque coin de rue.', '2025-06-09 22:10:00'),
(3, 25, '2025-07-12', 5, 'Le Duomo, les Offices et la meilleure glace de ma vie.', '2025-07-20 10:00:00'),
(3, 26, '2025-07-16', 3, 'Magnifique mais beaucoup de monde en juillet. À voir hors saison.', '2025-07-20 10:10:00'),
(1, 16, '2025-08-09', 4, 'La Sagrada Família est encore plus impressionnante de l''intérieur.', '2025-08-14 17:00:00'),
(2, 33, '2025-09-15', 4, 'Les musées gratuits sont une vraie chance. Le British Museum en premier.', '2025-09-21 16:00:00'),
(3, 4, '2025-11-29', 5, 'Le marché de Noël devant l''hôtel de ville et un concert à l''opéra.', '2025-12-04 20:00:00'),
(3, 5, '2025-12-01', 4, 'Petite ville superbe au pied des montagnes.', '2025-12-04 20:10:00'),
(2, 14, '2025-12-27', 5, 'Les chutes de la Lobé qui tombent directement dans la mer.', '2026-01-03 15:00:00'),
(3, 8, '2026-02-14', 4, 'Moins touristique que Bruges et tout aussi joli.', '2026-02-16 09:00:00'),
(1, 9, '2026-03-07', 5, 'Parfait pour un week-end, même sous la pluie.', '2026-03-09 08:30:00'),
(3, 34, '2026-04-18', 4, 'Montée à Arthur''s Seat : vue incroyable sur toute la ville.', '2026-04-22 19:45:00');
