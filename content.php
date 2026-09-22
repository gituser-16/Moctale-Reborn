<?php
require 'config/db.php';
session_start();

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$id]);
$movie = $stmt->fetch();

if (!$movie) {
    die("Movie not found.");
}

$isLoggedIn = isset($_SESSION['user_id']);
$isInWatchlist = false;

if ($isLoggedIn) {
    $checkStmt = $pdo->prepare("SELECT id FROM watchlist WHERE user_id = ? AND movie_id = ?");
    $checkStmt->execute([$_SESSION['user_id'], $movie['id']]);
    $isInWatchlist = $checkStmt->fetch() ? true : false;
}

// Handle adding/removing from watchlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn) {
    if (isset($_POST['add_watchlist'])) {
        $insert = $pdo->prepare("INSERT INTO watchlist (user_id, movie_id) VALUES (?, ?)");
        $insert->execute([$_SESSION['user_id'], $movie['id']]);
        $isInWatchlist = true;
    } elseif (isset($_POST['remove_watchlist'])) {
        $delete = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?");
        $delete->execute([$_SESSION['user_id'], $movie['id']]);
        $isInWatchlist = false;
    }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn && isset($_POST['submit_review'])) {
    $verdict = $_POST['verdict'] ?? '';
    $selectedVibes = $_POST['vibes'] ?? [];
    $comment = trim($_POST['comment'] ?? '');
    $vibesString = implode(", ", $selectedVibes);

    if ($verdict !== '' && $comment !== '') {
        $insertReview = $pdo->prepare("INSERT INTO reviews (user_id, movie_id, verdict, vibes, comment) VALUES (?, ?, ?, ?, ?)");
        $insertReview->execute([$_SESSION['user_id'], $movie['id'], $verdict, $vibesString, $comment]);
    }
}

// Fetch all reviews for this movie
$reviewsStmt = $pdo->prepare("
    SELECT reviews.*, users.name AS reviewer_name 
    FROM reviews 
    JOIN users ON reviews.user_id = users.id 
    WHERE movie_id = ? 
    ORDER BY reviews.created_at DESC
");
$reviewsStmt->execute([$movie['id']]);
$movieReviews = $reviewsStmt->fetchAll();

// Calculate Moctale Meter percentages
$verdictCounts = ['skip' => 0, 'timepass' => 0, 'watch' => 0, 'perfect' => 0];
foreach ($movieReviews as $review) {
    if (isset($verdictCounts[$review['verdict']])) {
        $verdictCounts[$review['verdict']]++;
    }
}
$totalVotes = array_sum($verdictCounts);

$verdictPercentages = [];
foreach ($verdictCounts as $key => $count) {
    $verdictPercentages[$key] = $totalVotes > 0 ? round(($count / $totalVotes) * 100) : 0;
}

// Calculate Vibe Chart percentages
$vibeCounts = ['Action' => 0, 'Thriller' => 0, 'Drama' => 0, 'Mystery' => 0, 'Romance' => 0];
foreach ($movieReviews as $review) {
    $reviewVibes = array_map('trim', explode(',', $review['vibes']));
    foreach ($reviewVibes as $v) {
        if (isset($vibeCounts[$v])) {
            $vibeCounts[$v]++;
        }
    }
}

$vibePercentages = [];
foreach ($vibeCounts as $key => $count) {
    $vibePercentages[$key] = $totalVotes > 0 ? round(($count / $totalVotes) * 100) : 0;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo htmlspecialchars($movie['title']); ?> - Moctale Reborn</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <a href="index.php" class="back-link">&larr; Back to Home</a>

    <div class="detail">
        <img src="https://image.tmdb.org/t/p/w400<?php echo $movie['poster_path']; ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
        <div class="info">
            <span class="category-tag"><?php echo ucwords(str_replace('_', ' ', $movie['category'])); ?></span>
            <h1><?php echo htmlspecialchars($movie['title']); ?></h1>

            <?php if ($totalVotes > 0): ?>
            <div style="margin: 20px 0; padding: 16px; background: var(--bg-card); border-radius: 10px; max-width: 500px;">
                <h4 style="margin-top:0;">Moctale Reborn Meter <span style="color:var(--text-muted); font-weight:normal; font-size:13px;">(<?php echo $totalVotes; ?> votes)</span></h4>
                <?php
                $verdictLabels = ['skip' => 'Skip', 'timepass' => 'Timepass', 'watch' => 'Definitely Watch', 'perfect' => 'Perfect Movie'];
                $verdictColors = ['skip' => '#f85149', 'timepass' => '#d29922', 'watch' => '#3fb950', 'perfect' => '#2dd4bf'];
                foreach ($verdictPercentages as $key => $pct):
                ?>
                    <div style="margin-bottom: 10px;">
                        <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px;">
                            <span><?php echo $verdictLabels[$key]; ?></span>
                            <span><?php echo $pct; ?>%</span>
                        </div>
                        <div style="background: #21262d; border-radius: 4px; height: 8px; overflow: hidden;">
                            <div style="width: <?php echo $pct; ?>%; background: <?php echo $verdictColors[$key]; ?>; height: 100%;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($totalVotes > 0): ?>
<div style="margin: 20px 0; padding: 16px; background: var(--bg-card); border-radius: 10px; max-width: 500px;">
    <h4 style="margin-top:0;">Vibe Check</h4>
    <?php
    $vibeColors = ['Action' => '#f85149', 'Thriller' => '#d29922', 'Drama' => '#818cf8', 'Mystery' => '#2dd4bf', 'Romance' => '#f778ba'];
    foreach ($vibePercentages as $vibe => $pct):
        if ($pct == 0) continue; // skip vibes nobody selected
    ?>
        <div style="margin-bottom: 10px;">
            <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px;">
                <span><?php echo $vibe; ?></span>
                <span><?php echo $pct; ?>%</span>
            </div>
            <div style="background: #21262d; border-radius: 4px; height: 8px; overflow: hidden;">
                <div style="width: <?php echo $pct; ?>%; background: <?php echo $vibeColors[$vibe]; ?>; height: 100%;"></div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div style="margin: 20px 0;">
    <?php if (!empty($movie['genres'])): ?>
        <p><strong>Genres:</strong> <?php echo htmlspecialchars($movie['genres']); ?></p>
    <?php endif; ?>

    <?php if (!empty($movie['director'])): ?>
        <p><strong>Director:</strong> <?php echo htmlspecialchars($movie['director']); ?></p>
    <?php endif; ?>

    <?php if (!empty($movie['tmdb_rating'])): ?>
        <p><strong>TMDB Rating:</strong> ⭐ <?php echo htmlspecialchars($movie['tmdb_rating']); ?>/10</p>
    <?php endif; ?>
</div>

<?php
// Decode cast JSON safely
$castList = [];
if (!empty($movie['cast'])) {
    $decoded = json_decode($movie['cast'], true);
    if (is_array($decoded)) {
        $castList = $decoded;
    }
}
?>

<?php if (!empty($castList)): ?>
<div style="margin: 20px 0;">
    <h4>Cast</h4>
    <div style="display: flex; gap: 14px; flex-wrap: wrap;">
        <?php foreach ($castList as $actor): ?>
            <div style="width: 90px; text-align: center;">
                <?php if (!empty($actor['photo'])): ?>
                    <img src="https://image.tmdb.org/t/p/w200<?php echo $actor['photo']; ?>" alt="<?php echo htmlspecialchars($actor['name']); ?>" style="width: 100%; border-radius: 8px;">
                <?php else: ?>
                    <div style="width: 90px; height: 130px; background: #21262d; border-radius: 8px; display:flex; align-items:center; justify-content:center; font-size:12px; color:var(--text-muted);">No Photo</div>
                <?php endif; ?>
                <p style="font-size: 12px; margin: 6px 0 2px; font-weight: 600;"><?php echo htmlspecialchars($actor['name']); ?></p>
                <p style="font-size: 11px; color: var(--text-muted); margin: 0;"><?php echo htmlspecialchars($actor['character']); ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

            <p><strong>Release Date:</strong> <?php echo htmlspecialchars($movie['release_date']); ?></p>
            <p><?php echo htmlspecialchars($movie['overview']); ?></p>

            <?php if ($isLoggedIn): ?>
                <form method="POST">
                    <?php if ($isInWatchlist): ?>
                        <button type="submit" name="remove_watchlist" class="watchlist-btn remove-btn">
                            ✓ In Watchlist (Remove)
                        </button>
                    <?php else: ?>
                        <button type="submit" name="add_watchlist" class="watchlist-btn add-btn">
                            + Add to Watchlist
                        </button>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <p style="color:#aaa; margin-top:15px;"><a href="login.php" style="color:#e50914;">Login</a> to add this to your watchlist.</p>
            <?php endif; ?>

            <?php if ($isLoggedIn): ?>
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #21262d;">
                    <h3>Write a Review</h3>
                    <form method="POST">
                        <p style="margin-bottom: 8px; color: var(--text-muted); font-size: 14px;">Your verdict:</p>
                        <div style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap;">
                            <label><input type="radio" name="verdict" value="skip" required> Skip</label>
                            <label><input type="radio" name="verdict" value="timepass"> Timepass</label>
                            <label><input type="radio" name="verdict" value="watch"> Definitely Watch</label>
                            <label><input type="radio" name="verdict" value="perfect"> Perfect Movie</label>
                        </div>

                        <p style="margin-bottom: 8px; color: var(--text-muted); font-size: 14px;">Which vibes fit this movie?</p>
                        <div style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap;">
                            <label><input type="checkbox" name="vibes[]" value="Action"> Action</label>
                            <label><input type="checkbox" name="vibes[]" value="Thriller"> Thriller</label>
                            <label><input type="checkbox" name="vibes[]" value="Drama"> Drama</label>
                            <label><input type="checkbox" name="vibes[]" value="Mystery"> Mystery</label>
                            <label><input type="checkbox" name="vibes[]" value="Romance"> Romance</label>
                        </div>

                        <textarea name="comment" placeholder="Write your thoughts..." required
                            style="width: 100%; max-width: 500px; padding: 10px; border-radius: 6px; border: 1px solid #30363d; background: var(--bg); color: var(--text-main); min-height: 80px; font-family: inherit;"></textarea><br><br>

                        <button type="submit" name="submit_review" class="watchlist-btn add-btn">Submit Review</button>
                    </form>
                </div>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #21262d;">
    <h3>User Reviews (<?php echo count($movieReviews); ?>)</h3>
    <?php if (empty($movieReviews)): ?>
        <p class="empty-state" style="padding:0;">No reviews yet. Be the first!</p>
    <?php else: ?>
        <?php foreach ($movieReviews as $review): ?>
            <div style="background: var(--bg-card); padding: 14px; border-radius: 8px; margin-bottom: 12px; max-width: 500px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <strong><?php echo htmlspecialchars($review['reviewer_name']); ?></strong>
                    <span style="font-size: 12px; color: var(--accent);"><?php echo ucwords($review['verdict']); ?></span>
                </div>
                <?php if (!empty($review['vibes'])): ?>
                    <p style="font-size: 12px; color: var(--text-muted); margin: 4px 0;"><?php echo htmlspecialchars($review['vibes']); ?></p>
                <?php endif; ?>
                <p style="margin: 6px 0 0; color: var(--text-main);"><?php echo htmlspecialchars($review['comment']); ?></p>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

            <?php endif; ?>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
</body>
</html>