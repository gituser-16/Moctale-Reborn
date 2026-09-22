<?php
require 'config/db.php';
session_start();

require 'includes/auth-check.php';

$stmt = $pdo->prepare("
    SELECT movies.* FROM movies
    JOIN watchlist ON movies.id = watchlist.movie_id
    WHERE watchlist.user_id = ?
    ORDER BY watchlist.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$watchlistMovies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My List - Moctale Reborn</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <h1>My List</h1>
        <a href="index.php" class="back-link">&larr; Back to Home</a>
    </div>

    <div class="carousel">
        <?php if (empty($watchlistMovies)): ?>
            <p style="padding: 0 20px; color:#aaa;">Your watchlist is empty. Go add some movies!</p>
        <?php else: ?>
            <?php foreach ($watchlistMovies as $movie): ?>
                <a href="content.php?id=<?php echo $movie['id']; ?>" style="text-decoration:none; color:white;">
                    <div class="card">
                        <img src="https://image.tmdb.org/t/p/w300<?php echo $movie['poster_path']; ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                        <p><?php echo htmlspecialchars($movie['title']); ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

<?php include 'includes/footer.php'; ?>
</body>
</html>