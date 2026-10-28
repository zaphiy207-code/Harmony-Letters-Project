<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../db_connect.php';

function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    respond(405, ['message' => 'Use POST to submit a review.']);
}

// Guests cannot submit reviews.
if (empty($_SESSION['user_id'])) {
    respond(401, [
        'message' => 'Please sign in or create an account to submit a review.',
        'signin' => '/harmony-letters/signin.php',
        'create_account' => '/harmony-letters/createacc.php'
    ]);
}

// Validate the CSRF token.
$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    !hash_equals($_SESSION['csrf_token'] ?? '', $token)
) {
    respond(403, [
        'message' => 'Your session token is invalid. Refresh the page and try again.'
    ]);
}

$songId = filter_var(
    $_POST['song_id'] ?? null,
    FILTER_VALIDATE_INT
);

$rating = filter_var(
    $_POST['rating'] ?? null,
    FILTER_VALIDATE_INT
);

$category = $_POST['category'] ?? '';
$reviewText = trim($_POST['review_text'] ?? '');

$allowedCategories = [
    'Emotional',
    'Lyrics',
    'Production',
    'Personal Story'
];

if (!$songId || $songId < 1) {
    respond(400, ['message' => 'Invalid song.']);
}

if ($rating === false || $rating < 1 || $rating > 5) {
    respond(400, ['message' => 'Please select a rating from 1 to 5 stars.']);
}

if (!is_string($category) ||
    !in_array($category, $allowedCategories, true)) {
    respond(400, ['message' => 'Please select a valid review category.']);
}

if ($reviewText === '' || mb_strlen($reviewText) > 5000) {
    respond(400, [
        'message' => 'Your review must contain text and be no longer than 5000 characters.'
    ]);
}

try {
    // Confirm that this song exists.
    $songCheck = $pdo->prepare(
        'SELECT song_id FROM songs WHERE song_id = ?'
    );
    $songCheck->execute([$songId]);

    if (!$songCheck->fetch()) {
        respond(404, ['message' => 'That song could not be found.']);
    }

    // Use the logged-in user's ID from the session.
    $userId = (int) $_SESSION['user_id'];

    $insert = $pdo->prepare(
        'INSERT INTO reviews
            (user_id, song_id, rating, review_text, category)
         VALUES
            (:user_id, :song_id, :rating, :review_text, :category)'
    );

    $insert->execute([
        'user_id' => $userId,
        'song_id' => $songId,
        'rating' => $rating,
        'review_text' => $reviewText,
        'category' => $category
    ]);

    respond(201, [
        'message' => 'Your review has been saved successfully!',
        'review_id' => (int) $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    // MySQL error 23000 commonly indicates a duplicate unique key.
    if ($e->getCode() === '23000') {
        respond(409, [
            'message' => 'You have already reviewed this song. Each account can submit one review per song.'
        ]);
    }

    error_log('Review submission failed: ' . $e->getMessage());

    respond(500, [
        'message' => 'Your review could not be saved. Please try again later.'
    ]);
}
