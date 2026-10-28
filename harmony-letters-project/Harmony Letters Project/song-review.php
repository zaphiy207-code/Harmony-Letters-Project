<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_connect.php';

$songId = filter_input(INPUT_GET, 'song_id', FILTER_VALIDATE_INT);

if (!$songId || $songId < 1) {
    http_response_code(400);
    exit('Invalid song ID.');
}

$stmt = $pdo->prepare("
    SELECT
        songs.song_id,
        songs.title AS song_title,
        albums.title AS album_title,
        albums.cover_url,
        artists.name AS artist_name
    FROM songs
    INNER JOIN albums
        ON songs.album_id = albums.album_id
    INNER JOIN artists
        ON albums.artist_id = artists.artist_id
    WHERE songs.song_id = :song_id
");

$stmt->execute(['song_id' => $songId]);
$song = $stmt->fetch();

if (!$song) {
    http_response_code(404);
    exit('Song not found.');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Review <?= h($song['song_title']) ?> | Harmony Letters Project</title>
<link rel="stylesheet" href="harmony.css">
<!-- to allow older browsers to load the website -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js">
</script>
<![endif]--> 
</head>

<body>
<div id="stars"></div>

<div class="site-top">
<header>
<h1>Harmony Letters Project</h1>
</header>

<nav class="navbar"> 
<ul>
<li><a href="index.html">Home</a></li>
<li><a href="albums.html">Albums</a></li>
<li><a href="reviews.html">Review</a></li>
<li><a href="signin.php">Sign In</a></li>
<li><a href="createacc.php">Create Account</a></li>
</nav>
</ul>
</div>

<div id="wrapper">
<main class="review-page">

<h1>Song Review</h1>
<p class="review-intro">
Share your thoughts and rate the song you listened to.
</p>

<div class="review-layout">

<section class="review-song-info">

<img
src="<?= h($song['cover_url']) ?>"
alt="<?= h($song['album_title']) ?> album cover"
class="review-album-cover"
id="reviewAlbumCover">

<h2 id="reviewSongTitle"><?= h($song['song_title']) ?></h2>
<p id="reviewArtistName"><?= h($song['artist_name']) ?></p>

<a href="javascript:history.back()" class="back-link"> Back to Song</a>

</section>

<section class="review-form-panel">

<h2>Rate This Song</h2>

<form id="songReviewForm">
<input
type="hidden"
id="csrfToken"
name="csrf_token"
value="<?= h($_SESSION['csrf_token']) ?>">

<input
type="hidden"
id="songId"
name="song_id"
value="<?= (int) $song['song_id'] ?>">

<fieldset class="rating-fieldset">

<legend>Your Rating</legend>

<div class="star-rating"
role="group"
aria-label="Rate this song from 1 to 5 stars">

<button type="button" class="star"
data-rating="1"
aria-label="1 star"
aria-pressed="false">★</button>

<button type="button" class="star"
data-rating="2"
aria-label="2 stars"
aria-pressed="false">★</button>

<button type="button" class="star"
data-rating="3"
aria-label="3 stars"
aria-pressed="false">★</button>

<button type="button" class="star"
data-rating="4"
aria-label="4 stars"
aria-pressed="false">★</button>

<button type="button" class="star"
data-rating="5"
aria-label="5 stars"
aria-pressed="false">★</button>

</div>

<p id="ratingText" aria-live="polite">
Select a rating from 1 to 5 stars.
</p>

</fieldset>

<div class="review-input">
<label for="reviewCategory">Review Category</label>

<select id="reviewCategory" name="reviewCategory" required>
<option value="Emotional">Emotional</option>
<option value="Lyrics">Lyrics</option>
<option value="Production">Production</option>
<option value="Personal Story">Personal Story</option>
</select>
</div>

<div class="review-input">
<label for="reviewText">
Your Review
</label>

<textarea
id="reviewText"
name="reviewText"
rows="7"
placeholder="What do you think about this song?"
required></textarea>
</div>

<button type="submit" class="submit">
Submit
</button>

<p id="reviewMessage"
class="review-message"
aria-live="polite"></p>

</form>

</section>

</div>

</main>

<div class="return-home">
<a href="albums.html">Return to Albums</a>
</div>
</div>

<footer>
<p>© 2026 Harmony Letters Project. All rights reserved.</p>
</footer>

</div>

<script src="js/review.js"></script>
</body>
</html>
