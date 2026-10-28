<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db_connect.php';

requireAdmin();

$tables = [
    'users' => [
        'label' => 'Users',
        'pk' => 'user_id',
        'fields' => [
            'username' => 'Username',
            'email' => 'Email',
            'role' => 'Role (user or admin)'
        ],
        'editable' => false
    ],
    'artists' => [
        'label' => 'Artists',
        'pk' => 'artist_id',
        'fields' => [
            'name' => 'Artist name',
            'country' => 'Country',
            'bio' => 'Biography'
        ],
        'editable' => true
    ],
    'albums' => [
        'label' => 'Albums',
        'pk' => 'album_id',
        'fields' => [
            'artist_id' => 'Artist ID',
            'title' => 'Album title',
            'release_year' => 'Release year',
            'genre' => 'Genre',
            'description' => 'Description',
            'cover_url' => 'Cover image URL'
        ],
        'editable' => true
    ],
    'songs' => [
        'label' => 'Songs',
        'pk' => 'song_id',
        'fields' => [
            'album_id' => 'Album ID',
            'track_number' => 'Track number',
            'title' => 'Song title',
            'duration' => 'Duration',
            'audio_file' => 'Audio file path',
            'play_url' => 'Play URL'
        ],
        'editable' => true
    ],
    'reviews' => [
        'label' => 'Reviews',
        'pk' => 'review_id',
        'fields' => [
            'user_id' => 'User ID',
            'song_id' => 'Song ID',
            'rating' => 'Rating (1–5)',
            'category' => 'Category',
            'review_text' => 'Review text'
        ],
        'editable' => true
    ]
];

$selected = $_GET['table'] ?? 'artists';

if (!isset($tables[$selected])) {
    $selected = 'artists';
}

$config = $tables[$selected];
$message = '';
$error = '';

function postValue(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $action = $_POST['action'] ?? '';
    $table = $_POST['table'] ?? '';

    if (!isset($tables[$table])) {
        http_response_code(400);
        exit('Invalid table.');
    }

    $cfg = $tables[$table];
    $pk = $cfg['pk'];
    $selected = $table;
    $config = $cfg;

    try {
        if ($action === 'delete') {
            if ($table === 'users') {
                $id = filter_var(
                    $_POST['id'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (!$id) {
                    throw new RuntimeException('Invalid record ID.');
                }

                if ((int) $id === (int) $_SESSION['user_id']) {
                    throw new RuntimeException(
                        'You cannot delete your own account here.'
                    );
                }

                $check = $pdo->prepare(
                    'SELECT role FROM users WHERE user_id = ?'
                );
                $check->execute([$id]);
                $target = $check->fetch();

                if (!$target) {
                    throw new RuntimeException('User not found.');
                }

                if ($target['role'] === 'admin') {
                    throw new RuntimeException(
                        'Admin accounts cannot be deleted from this page.'
                    );
                }

                $stmt = $pdo->prepare(
                    'DELETE FROM users WHERE user_id = ?'
                );
                $stmt->execute([$id]);
            } elseif ($cfg['editable']) {
                $id = filter_var(
                    $_POST['id'] ?? null,
                    FILTER_VALIDATE_INT
                );

                if (!$id) {
                    throw new RuntimeException('Invalid record ID.');
                }

                $stmt = $pdo->prepare(
                    "DELETE FROM `$table` WHERE `$pk` = ?"
                );
                $stmt->execute([$id]);
            } else {
                throw new RuntimeException('Delete is not available.');
            }

            $message = 'Record deleted.';
        } elseif ($action === 'save' && $cfg['editable']) {
            $id = filter_var(
                $_POST['id'] ?? '',
                FILTER_VALIDATE_INT
            );

            $values = [];

            foreach ($cfg['fields'] as $field => $label) {
                $values[$field] = postValue($field);
            }

            if ($table === 'reviews') {
                $rating = filter_var(
                    $values['rating'],
                    FILTER_VALIDATE_INT
                );

                if (!$rating || $rating < 1 || $rating > 5) {
                    throw new RuntimeException(
                        'Rating must be a number from 1 to 5.'
                    );
                }

                $values['rating'] = (string) $rating;
            }

            foreach (['artist_id', 'album_id', 'track_number',
                      'user_id', 'song_id', 'release_year'] as $numeric) {
                if (isset($values[$numeric]) &&
                    $values[$numeric] !== '' &&
                    !ctype_digit($values[$numeric])) {
                    throw new RuntimeException(
                        "$numeric must be a positive whole number."
                    );
                }
            }

            if ($table === 'reviews' &&
                ($values['user_id'] === '' || $values['song_id'] === '')) {
                throw new RuntimeException(
                    'User ID and song ID are required.'
                );
            }

            if ($table === 'albums' &&
                ($values['artist_id'] === '' || $values['title'] === '')) {
                throw new RuntimeException(
                    'Artist ID and album title are required.'
                );
            }

            if ($table === 'songs' &&
                ($values['album_id'] === '' || $values['title'] === '')) {
                throw new RuntimeException(
                    'Album ID and song title are required.'
                );
            }

            if ($table === 'artists' && $values['name'] === '') {
                throw new RuntimeException('Artist name is required.');
            }

            $editing = $id !== false && $id !== null && $id > 0;

            if ($editing) {
                $sets = [];
                foreach (array_keys($values) as $field) {
                    $sets[] = "`$field` = ?";
                }

                $sql = "UPDATE `$table` SET " .
                    implode(', ', $sets) . " WHERE `$pk` = ?";

                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ...array_values($values),
                    $id
                ]);
                $message = 'Record updated.';
            } else {
                $columns = array_keys($values);
                $columnSql = implode(
                    ', ',
                    array_map(fn($field) => "`$field`", $columns)
                );
                $placeholders = implode(
                    ', ',
                    array_fill(0, count($columns), '?')
                );

                $stmt = $pdo->prepare(
                    "INSERT INTO `$table` ($columnSql)
                     VALUES ($placeholders)"
                );
                $stmt->execute(array_values($values));
                $message = 'Record created.';
            }
        } else {
            throw new RuntimeException('Invalid action.');
        }
    } catch (Throwable $e) {
        if ($e instanceof RuntimeException) {
            $error = $e->getMessage();
        } else {
            error_log('Admin action failed: ' . $e->getMessage());
            $error = 'The operation failed. Check IDs, required fields and database constraints.';
        }
    }
}

$editId = filter_var(
    $_GET['edit'] ?? '',
    FILTER_VALIDATE_INT
);
$editRecord = null;

if ($editId && $config['editable']) {
    $stmt = $pdo->prepare(
        "SELECT * FROM `$selected` WHERE `{$config['pk']}` = ?"
    );
    $stmt->execute([$editId]);
    $editRecord = $stmt->fetch();

    if (!$editRecord) {
        $error = 'The selected record could not be found.';
    }
}

$stats = [];
foreach ([
    'users' => 'users',
    'artists' => 'artists',
    'albums' => 'albums',
    'songs' => 'songs',
    'reviews' => 'reviews'
] as $label => $tableName) {
    $stats[$label] = (int) $pdo
        ->query("SELECT COUNT(*) FROM `$tableName`")
        ->fetchColumn();
}

$report = $pdo->query("
    SELECT
        s.title AS song,
        a.name AS artist,
        COUNT(r.review_id) AS review_count,
        ROUND(AVG(r.rating), 2) AS average_rating
    FROM songs s
    JOIN albums al ON s.album_id = al.album_id
    JOIN artists a ON al.artist_id = a.artist_id
    LEFT JOIN reviews r ON s.song_id = r.song_id
    GROUP BY s.song_id, s.title, a.name
    ORDER BY review_count DESC, s.title ASC
")->fetchAll();

if (isset($_GET['edit'])) {
    $message = '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard | Harmony Letters Project</title>
<link rel="stylesheet" href="harmony.css">

</head>
<body>
<main class="admin-page">
    <h1>Harmony Letters — Admin Dashboard</h1>
    <p>
        Signed in as <strong><?= h($_SESSION['username']) ?></strong>.
</p>

<div class="admin-top-links">    
<a href="index.html" class="admin-button">View Website</a>
<a href="logout.php"class="admin-button">Logout</a>
</div>
    

    <?php if ($message): ?>
        <p class="admin-alert" role="status"><?= h($message) ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="admin-error" role="alert"><?= h($error) ?></p>
    <?php endif; ?>

    <h2>Database Overview</h2>
    <div class="admin-stats">
    <?php foreach ($stats as $name => $count): ?>
    <div class="admin-stat">
    <strong><?= h(ucfirst($name)) ?></strong>
    <p><?= h($count) ?> records</p>
    </div>
    <?php endforeach; ?>
    </div>

    <section class="admin-panel">
    <h2>Manage Records</h2>
    <nav class="admin-nav" aria-label="Admin sections">
    <?php foreach ($tables as $name => $cfg): ?>
    <a href="admin.php?table=<?= h($name) ?>">
    <?= h($cfg['label']) ?>
    </a>

        <?php endforeach; ?>
        <a href="#report">Review Report</a>
        </nav>

        <h3><?= h($config['label']) ?></h3>

        <?php if ($config['editable']): ?>
            <h4><?= $editRecord ? 'Edit Record' : 'Create Record' ?></h4>
            <form class="admin-form" method="post" action="admin.php?table=<?= h($selected) ?>">
                <input type="hidden" name="csrf_token"
                       value="<?= h($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="table" value="<?= h($selected) ?>">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id"
                       value="<?= h($editRecord[$config['pk']] ?? '') ?>">

                <?php foreach ($config['fields'] as $field => $label): ?>
                <label for="<?= h($field) ?>"><?= h($label) ?></label>
                <?php if (in_array($field, ['bio', 'description', 'review_text'], true)): ?>
                <textarea id="<?= h($field) ?>"
                                  name="<?= h($field) ?>"><?= h($editRecord[$field] ?? '') ?></textarea>
                    <?php else: ?>
                        <input id="<?= h($field) ?>"
                               name="<?= h($field) ?>"
                               value="<?= h($editRecord[$field] ?? '') ?>"
                               <?= in_array($field, [
                                   'artist_id', 'album_id', 'track_number',
                                   'release_year', 'user_id', 'song_id', 'rating'
                               ], true) ? 'type="number"' : 'type="text"' ?>
                               <?= in_array($field, ['name', 'title'], true) ? 'required' : '' ?>
                               <?= $field === 'rating' ? 'min="1" max="5"' : '' ?>>
                    <?php endif; ?>
                <?php endforeach; ?>

                <p>
                    <button class="admin-button" type="submit">
                        <?= $editRecord ? 'Save Changes' : 'Create Record' ?>
                    </button>
                    <?php if ($editRecord): ?>
                        <a class="admin-button"
                           href="admin.php?table=<?= h($selected) ?>">Cancel</a>
                    <?php endif; ?>
                </p>
            </form>
        <?php else: ?>
        <p>Users are displayed below. Register users through the Create Account page.</p>
        <?php endif; ?>

        <h4>Existing Records</h4>
        <div class="admin-table-wrap">
        <table class="admin-table">
        <thead><tr>
        <th>ID</th>
        <?php foreach ($config['fields'] as $field => $label): ?>
        <th><?= h($label) ?></th>
        <?php endforeach; ?>
        <th>Actions</th>
        </tr></thead>
        <tbody>
        <?php
            $records = $pdo->query(
                "SELECT * FROM `$selected` ORDER BY `{$config['pk']}` DESC LIMIT 200"
            )->fetchAll();
            ?>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td><?= h($record[$config['pk']]) ?></td>
                    <?php foreach ($config['fields'] as $field => $label): ?>
                    <td><?= h($record[$field] ?? '') ?></td>
                    <?php endforeach; ?>
                    <td><div class="admin-actions">
                    <?php if ($config['editable']): ?>
                    <a class="admin-button"
                     href="admin.php?table=<?= h($selected) ?>&edit=<?= h($record[$config['pk']]) ?>">Edit</a>
                     <?php endif; ?>

                        <?php if ($config['editable'] || $selected === 'users'): ?>
                            <form method="post"
                                 action="admin.php?table=<?= h($selected) ?>"
                                 onsubmit="return confirm('Delete this record? This may also delete related records.');">
                                <input type="hidden" name="csrf_token"
                                       value="<?= h($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="table" value="<?= h($selected) ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id"
                                       value="<?= h($record[$config['pk']]) ?>">
                                <button class="admin-button" type="submit">Delete</button>
						    </form>
					          <?php endif; ?>
					        </div></td>
					     </tr>
					<?php endforeach; ?>
				     <?php if (!$records): ?>
				<tr><td colspan="<?= count($config['fields']) + 2 ?>">No records found.</td></tr>
			     <?php endif; ?>
			</tbody>
		</table>
	</div>
</section>

    <section class="admin-panel" id="report">
        <h2>Song Review Summary Report</h2>
        <p>Shows the number of reviews and average rating for each song.</p>
        <div class="admin-table-wrap">
        <table class="admin-table">
        <thead><tr>
        <th>Song</th><th>Artist</th>
        <th>Review Count</th><th>Average Rating</th>
            </tr></thead>
            <tbody>
            <?php foreach ($report as $row): ?>
                <tr>
                    <td><?= h($row['song']) ?></td>
                    <td><?= h($row['artist']) ?></td>
                    <td><?= h($row['review_count']) ?></td>
                    <td><?= $row['average_rating'] === null
                        ? 'No ratings'
                        : h($row['average_rating']) . ' / 5' ?></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
				</div>
				<p><button class="admin-button" onclick="window.print()" type="button">Print Report</button></p>
			</section>
		</main>
	</body>
</html>
