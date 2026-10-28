
<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../db_connect.php';

try {
    $sql = "
        SELECT
            r.review_id AS id,
            u.username AS username,
            s.title AS song,
            a.name AS artist,
            r.rating AS rating,
            r.category AS category,
            r.review_text AS text,
            r.created_at AS date,
            COALESCE(SUM(
                CASE WHEN rv.vote = 'up' THEN 1 ELSE 0 END
            ), 0) AS helpful,
            COALESCE(SUM(
                CASE WHEN rv.vote = 'down' THEN 1 ELSE 0 END
            ), 0) AS notHelpful
        FROM reviews r
        INNER JOIN users u
            ON r.user_id = u.user_id
        INNER JOIN songs s
            ON r.song_id = s.song_id
        INNER JOIN albums al
            ON s.album_id = al.album_id
        INNER JOIN artists a
            ON al.artist_id = a.artist_id
        LEFT JOIN review_votes rv
            ON r.review_id = rv.review_id
        GROUP BY
            r.review_id,
            u.username,
            s.title,
            a.name,
            r.rating,
            r.category,
            r.review_text,
            r.created_at
        ORDER BY r.created_at DESC
    ";

    $stmt = $pdo->query($sql);
    $reviews = $stmt->fetchAll();

    $myVotes = [];

    if (isset($_SESSION['user_id'])) {
        $voteStmt = $pdo->prepare("
            SELECT review_id, vote
            FROM review_votes
            WHERE user_id = :user_id
        ");

        $voteStmt->execute([
            'user_id' => $_SESSION['user_id']
        ]);

        foreach ($voteStmt->fetchAll() as $vote) {
            $myVotes[(string) $vote['review_id']] = $vote['vote'];
        }
    }

    echo json_encode([
        'reviews' => $reviews,
        'myVotes' => $myVotes
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    error_log('Get reviews failed: ' . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        'message' => 'Unable to retrieve reviews.'
    ]);
}
