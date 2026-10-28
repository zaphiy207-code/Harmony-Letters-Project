-- Jonathan Tan (B20260483) 

SET NAMES utf8mb4;
DROP DATABASE IF EXISTS webdevmusicrater;
CREATE DATABASE webdevmusicrater CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE webdevmusicrater;

CREATE TABLE users (
  user_id       INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  email         VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE artists (
  artist_id INT AUTO_INCREMENT PRIMARY KEY,
  name      VARCHAR(100) NOT NULL UNIQUE,
  country   VARCHAR(50),
  bio       TEXT
);

CREATE TABLE albums (
  album_id     INT AUTO_INCREMENT PRIMARY KEY,
  artist_id    INT NOT NULL,
  title        VARCHAR(150) NOT NULL,
  release_year INT CHECK (release_year BETWEEN 1900 AND 2100),
  genre        VARCHAR(50),
  description  TEXT,
  cover_url    VARCHAR(255),
  UNIQUE (artist_id, title),
  FOREIGN KEY (artist_id) REFERENCES artists(artist_id) ON DELETE CASCADE
);

CREATE TABLE songs (
  song_id      INT AUTO_INCREMENT PRIMARY KEY,
  album_id     INT NOT NULL,
  track_number INT NOT NULL CHECK (track_number > 0),
  title        VARCHAR(150) NOT NULL,
  duration     VARCHAR(10),
  audio_file   VARCHAR(255),
  play_url     VARCHAR(255),
  UNIQUE (album_id, track_number),
  FOREIGN KEY (album_id) REFERENCES albums(album_id) ON DELETE CASCADE
);

CREATE TABLE reviews (
  review_id   INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  song_id     INT NOT NULL,
  rating      TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  review_text VARCHAR(1000),
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (user_id, song_id),
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (song_id) REFERENCES songs(song_id) ON DELETE CASCADE
);

CREATE VIEW song_details AS
SELECT s.song_id, s.track_number, s.title AS song_title, s.duration, s.audio_file, s.play_url,
       al.album_id, al.title AS album_title, al.cover_url, al.release_year, al.genre,
       ar.artist_id, ar.name AS artist_name,
       r.avg_rating,
       COALESCE(r.review_count, 0) AS review_count
FROM songs s
JOIN albums  al ON al.album_id  = s.album_id
JOIN artists ar ON ar.artist_id = al.artist_id
LEFT JOIN (SELECT song_id, ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS review_count
           FROM reviews GROUP BY song_id) r ON r.song_id = s.song_id;

INSERT INTO users (user_id, username, email, password_hash, role, created_at) VALUES
(1, 'admin', 'admin@musicrater.test', '$2y$10$YIfoX5X9UQZQcEtarMaHlejSUlyQTLZGY0u2BWDvuhjlVBNdHKhLK', 'admin', '2026-05-01 09:00:00'),
(2, 'moderator', 'moderator@musicrater.test', '$2y$10$YIfoX5X9UQZQcEtarMaHlejSUlyQTLZGY0u2BWDvuhjlVBNdHKhLK', 'admin', '2026-05-04 10:00:00'),
(3, 'alya', 'alya@musicrater.test', '$2y$10$Xmsr9CbHk472JUcpCiJMqeA.7xSMoDbjVDy8N3ypB10ZYR.xf/qDe', 'user', '2026-05-07 11:00:00'),
(4, 'hakim', 'hakim@musicrater.test', '$2y$10$Xmsr9CbHk472JUcpCiJMqeA.7xSMoDbjVDy8N3ypB10ZYR.xf/qDe', 'user', '2026-05-10 12:00:00'),
(5, 'sofia', 'sofia@musicrater.test', '$2y$10$Xmsr9CbHk472JUcpCiJMqeA.7xSMoDbjVDy8N3ypB10ZYR.xf/qDe', 'user', '2026-05-13 13:00:00'),
(6, 'danial', 'danial@musicrater.test', '$2y$10$Xmsr9CbHk472JUcpCiJMqeA.7xSMoDbjVDy8N3ypB10ZYR.xf/qDe', 'user', '2026-05-16 14:00:00'),
(7, 'nurul', 'nurul@musicrater.test', '$2y$10$Xmsr9CbHk472JUcpCiJMqeA.7xSMoDbjVDy8N3ypB10ZYR.xf/qDe', 'user', '2026-05-19 15:00:00'),
(8, 'aiman', 'aiman@musicrater.test', '$2y$10$Xmsr9CbHk472JUcpCiJMqeA.7xSMoDbjVDy8N3ypB10ZYR.xf/qDe', 'user', '2026-05-22 16:00:00'),
(9, 'farah', 'farah@musicrater.test', '$2y$10$Xmsr9CbHk472JUcpCiJMqeA.7xSMoDbjVDy8N3ypB10ZYR.xf/qDe', 'user', '2026-05-25 17:00:00'),
(10, 'irfan', 'irfan@musicrater.test', '$2y$10$Xmsr9CbHk472JUcpCiJMqeA.7xSMoDbjVDy8N3ypB10ZYR.xf/qDe', 'user', '2026-05-28 18:00:00');

INSERT INTO artists (artist_id, name, country, bio) VALUES
(1, 'Sleeping at Last', 'USA', 'The music project of Chicago singer-songwriter Ryan O''Neal, known for cinematic, orchestral songs.'),
(2, 'Novo Amor', 'Wales', 'The project of Welsh multi-instrumentalist Ali John Meredith-Lacey, known for falsetto vocals and atmospheric indie folk.');

INSERT INTO albums (album_id, artist_id, title, release_year, genre, description, cover_url) VALUES
(1, 1, 'Atlas: Year One', 2014, 'Indie / Ambient', 'An introspective collection exploring human emotions, personal growth, and our place in the universe.', 'pictures/sleepingatlast.jpg'),
(2, 2, 'Birthplace', 2018, 'Indie Folk', 'An atmospheric album filled with delicate vocals, layered instrumentals, and reflective songwriting.', 'pictures/novoamor.webp');

INSERT INTO songs (song_id, album_id, track_number, title, audio_file, play_url) VALUES
(1, 1, 1, 'Overture', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Overture'),
(2, 1, 2, 'Woodwork', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Woodwork'),
(3, 1, 3, 'I''ll Keep You Safe', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+I%27ll+Keep+You+Safe'),
(4, 1, 4, 'Bad Blood', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Bad+Blood'),
(5, 1, 5, 'Uneven Odds', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Uneven+Odds'),
(6, 1, 6, 'Light', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Light'),
(7, 1, 7, 'You Are Enough', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+You+Are+Enough'),
(8, 1, 8, 'Heirloom', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Heirloom'),
(9, 1, 9, 'The Projectionist', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+The+Projectionist'),
(10, 1, 10, 'In the Embers', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+In+the+Embers'),
(11, 1, 11, 'Sun', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Sun'),
(12, 1, 12, 'Mercury', 'audio/Mercury.mp3', 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Mercury'),
(13, 1, 13, 'Venus', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Venus'),
(14, 1, 14, 'Earth', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Earth'),
(15, 1, 15, 'Moon', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Moon'),
(16, 1, 16, 'Mars', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Mars'),
(17, 1, 17, 'Jupiter', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Jupiter'),
(18, 1, 18, 'Saturn', 'audio/Saturn.mp3', 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Saturn'),
(19, 1, 19, 'Uranus', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Uranus'),
(20, 1, 20, 'Neptune', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Neptune'),
(21, 1, 21, 'Pluto', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Pluto'),
(22, 1, 22, 'North', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+North'),
(23, 1, 23, 'South', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+South'),
(24, 1, 24, 'East', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+East'),
(25, 1, 25, 'West', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+West'),
(26, 1, 26, 'Pacific', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Pacific'),
(27, 1, 27, 'Atlantic', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Atlantic'),
(28, 1, 28, 'Indian', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Indian'),
(29, 1, 29, 'Southern', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Southern'),
(30, 1, 30, 'Arctic', NULL, 'https://www.youtube.com/results?search_query=Sleeping+at+Last+Arctic'),
(31, 2, 1, 'Emigrate', NULL, 'https://www.youtube.com/results?search_query=Novo+Amor+Emigrate'),
(32, 2, 2, 'Birthplace', NULL, 'https://www.youtube.com/results?search_query=Novo+Amor+Birthplace'),
(33, 2, 3, 'Utican', NULL, 'https://www.youtube.com/results?search_query=Novo+Amor+Utican'),
(34, 2, 4, 'Seneca', NULL, 'https://www.youtube.com/results?search_query=Novo+Amor+Seneca'),
(35, 2, 5, 'Anniversary', NULL, 'https://www.youtube.com/results?search_query=Novo+Amor+Anniversary'),
(36, 2, 6, '13494', NULL, 'https://www.youtube.com/results?search_query=Novo+Amor+13494'),
(37, 2, 7, 'State Lines', NULL, 'https://www.youtube.com/results?search_query=Novo+Amor+State+Lines'),
(38, 2, 8, 'Sleepless', NULL, 'https://www.youtube.com/results?search_query=Novo+Amor+Sleepless'),
(39, 2, 9, 'Repeat Until Death', NULL, 'https://www.youtube.com/results?search_query=Novo+Amor+Repeat+Until+Death'),
(40, 2, 10, 'Oh, Round Lake', NULL, 'https://www.youtube.com/results?search_query=Novo+Amor+Oh%2C+Round+Lake');

INSERT INTO reviews (user_id, song_id, rating, review_text, created_at) VALUES
(3, 31, 3, 'its alright', '2026-06-02 02:05:49'),
(4, 37, 5, 'chills every time', '2026-06-04 11:13:42'),
(4, 8, 5, 'those strings 😭', '2026-06-10 15:18:07'),
(5, 35, 5, 'so gentle', '2026-06-11 13:53:47'),
(9, 19, 5, 'that ending man', '2026-06-14 04:23:00'),
(7, 32, 2, 'too slow but nice voice', '2026-06-14 16:48:27'),
(10, 25, 5, 'feels like home', '2026-06-17 13:13:32'),
(6, 14, 5, 'masterpiece', '2026-06-20 09:40:04'),
(4, 32, 5, 'that falsetto is insane', '2026-06-22 16:59:40'),
(5, 6, 2, 'bit boring tbh', '2026-06-23 16:03:20'),
(6, 39, 5, 'most played this month', '2026-06-25 00:37:11'),
(8, 29, 5, 'beautifully produced', '2026-06-26 13:38:02'),
(4, 33, 3, 'its okay', '2026-06-27 08:20:49'),
(7, 3, 2, 'too quiet for me', '2026-07-05 03:52:24'),
(4, 13, 5, 'perfect rainy day song', '2026-07-05 13:18:40'),
(4, 11, 5, 'on repeat', '2026-07-06 20:36:35'),
(8, 37, 5, 'road trip essential', '2026-07-09 07:20:33'),
(7, 2, 4, 'calm, good study song', '2026-07-15 06:59:12'),
(8, 18, 5, 'headphones on, trust', '2026-07-16 08:06:07'),
(5, 3, 3, 'its ok ig', '2026-07-17 04:48:49'),
(9, 13, 5, 'sounds like a movie', '2026-07-17 23:50:34'),
(7, 16, 3, 'mid', '2026-07-21 20:03:28'),
(5, 31, 3, 'bit repetitive', '2026-07-26 07:31:03'),
(8, 38, 3, 'the intro carries', '2026-07-26 10:51:39'),
(6, 16, 2, 'too slow', '2026-08-06 14:09:58'),
(8, 10, 3, 'not my style', '2026-08-07 03:51:53'),
(10, 6, 3, 'decent, zoned out halfway', '2026-08-08 05:40:55'),
(10, 30, 4, 'late night vibes', '2026-08-10 04:16:43'),
(3, 37, 4, 'grows on you', '2026-08-11 07:23:28'),
(4, 18, 5, 'his vocals are unmatched', '2026-08-14 06:32:27'),
(5, 27, 5, 'obsessed', '2026-08-14 20:57:54'),
(9, 18, 3, 'expected to cry, didnt', '2026-08-21 05:21:31'),
(9, 16, 3, 'fine i guess', '2026-08-23 14:07:22'),
(3, 32, 2, 'not for me', '2026-08-24 02:54:11'),
(3, 39, 4, 'actually really nice', '2026-08-27 19:45:47'),
(9, 21, 5, 'deserves more listens', '2026-08-29 04:50:04'),
(4, 35, 5, '10/10 no notes', '2026-08-31 10:57:11'),
(4, 3, 5, 'so soft omg', '2026-09-02 02:02:23'),
(10, 5, 5, 'underrated fr', '2026-09-03 23:39:41'),
(9, 32, 5, 'played it 3 times in a row', '2026-09-06 05:21:15'),
(10, 31, 5, 'instant calm', '2026-09-07 21:31:21'),
(5, 18, 5, 'the piano tho', '2026-09-09 04:28:49'),
(6, 38, 3, 'relaxing but forgettable', '2026-09-09 14:27:44'),
(8, 32, 4, 'love the layers', '2026-09-11 02:32:03'),
(8, 30, 5, 'perfect closer', '2026-09-12 10:38:44'),
(3, 14, 1, 'couldnt get into it', '2026-09-12 16:08:52'),
(10, 1, 4, 'nice intro to the whole thing', '2026-09-12 23:54:54'),
(10, 18, 4, 'the ending is so good', '2026-09-13 11:27:35'),
(9, 39, 4, 'late night favourite', '2026-09-14 10:26:30'),
(5, 32, 3, 'good background music', '2026-09-20 15:03:11'),
(8, 35, 4, 'wish it was longer', '2026-09-21 06:23:57'),
(9, 3, 5, 'made me cry ngl', '2026-09-22 02:29:21'),
(7, 27, 5, 'didnt expect to love this', '2026-10-02 10:13:18'),
(8, 39, 4, 'great with headphones', '2026-10-02 18:39:41'),
(10, 16, 4, 'good walking song', '2026-10-04 11:02:09'),
(3, 8, 4, 'lowkey grew on me', '2026-10-09 03:27:17'),
(10, 36, 4, 'better on second listen', '2026-10-10 15:21:40'),
(6, 18, 4, 'really pretty', '2026-10-11 14:27:31'),
(3, 6, 3, 'kinda overhyped', '2026-10-12 04:49:20'),
(10, 33, 3, 'kinda flat', '2026-10-14 18:45:50');


ALTER TABLE reviews
ADD COLUMN category VARCHAR(50) NOT NULL DEFAULT 'General';

CREATE TABLE review_votes (
    vote_id INT AUTO_INCREMENT PRIMARY KEY,
    review_id INT NOT NULL,
    user_id INT NOT NULL,
    vote ENUM('up', 'down') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (review_id, user_id),

    FOREIGN KEY (review_id)
        REFERENCES reviews(review_id)
        ON DELETE CASCADE,

    FOREIGN KEY (user_id)

        REFERENCES users(user_id)
        ON DELETE CASCADE
);

