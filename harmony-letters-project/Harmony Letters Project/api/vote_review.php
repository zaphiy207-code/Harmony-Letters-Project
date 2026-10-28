<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['message' => 'Method not allowed.']);
    exit;
}

$userId = $_SESSION['user_id'] ?? null;

if ($userId === null) {
    http_response_code(401);
    echo json_encode([
        'message' => 'Please sign in before voting.'
    ]);
    exit;
}

try {
    $input = json_decode(
        file_get_contents('php://input'),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    $reviewId = filter_var(
        $input['review_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $vote = $input['vote'] ?? '';

    if (!$reviewId || !in_array($vote, ['up', 'down'], true)) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid vote.']);
        exit;
    }

    $check = $pdo->prepare(
        'SELECT review_id FROM reviews WHERE review_id = ?'
    );
    $check->execute([$reviewId]);

    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['message' => 'Review not found.']);
        exit;
    }

    $findVote = $pdo->prepare(
        'SELECT vote FROM review_votes
         WHERE review_id = ? AND user_id = ?'
    );
    $findVote->execute([$reviewId, $userId]);
    $existingVote = $findVote->fetchColumn();

    if ($existingVote === $vote) {
        // Clicking the same vote again removes it.
        $delete = $pdo->prepare(
            'DELETE FROM review_votes
             WHERE review_id = ? AND user_id = ?'
        );
        $delete->execute([$reviewId, $userId]);
    } else {
        $save = $pdo->prepare(
            'INSERT INTO review_votes (review_id, user_id, vote)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE
             vote = VALUES(vote)'
        );
        $save->execute([$reviewId, $userId, $vote]);
    }

    echo json_encode(['success' => true]);

} catch (JsonException $e) {
    http_response_code(400);
    echo json_encode(['message' => 'Invalid JSON request.']);

} catch (Throwable $e) {
    error_log('Vote failed: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode(['message' => 'Unable to save your vote.']);
}
