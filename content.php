<?php
require 'config/db.php';
session_start();

require 'includes/auth-check.php';

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
        </div>
    </div>
</body>
</html>