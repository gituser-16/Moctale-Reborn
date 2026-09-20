<?php
require 'config/db.php';
session_start();

// Handle search
$searchTerm = $_GET['search'] ?? '';
$searchResults = [];

if ($searchTerm !== '') {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE title ILIKE ?");
    $stmt->execute(["%$searchTerm%"]);
    $searchResults = $stmt->fetchAll();
}

// Fetch movies grouped by category (only needed when not searching)
$categories = ['trending', 'popular', 'top_rated'];
$moviesByCategory = [];

foreach ($categories as $cat) {
    $stmt = $pdo->prepare("SELECT * FROM movies WHERE category = ? ORDER BY release_date DESC");
    $stmt->execute([$cat]);
    $moviesByCategory[$cat] = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Moctale Reborn</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="header">
        <h1>Moctale Reborn</h1>
        <div>
            <?php if (isset($_SESSION['user_name'])): ?>
                Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                | <a href="logout.php" style="color:white;">Logout</a>
                | <a href="watchlist.php" style="color:white;">My List</a>
            <?php else: ?>
                <a href="login.php" style="color:white;">Login</a>
            <?php endif; ?>
        </div>
    </div>

    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search movies..." value="<?php echo htmlspecialchars($searchTerm); ?>">
        <button type="submit">Search</button>
    </form>

    <?php if ($searchTerm !== ''): ?>
        <h2 class="row-title">Search Results for "<?php echo htmlspecialchars($searchTerm); ?>"</h2>
        <div class="carousel">
            <?php if (empty($searchResults)): ?>
                <p style="color:#aaa;">No movies found.</p>
            <?php else: ?>
                <?php foreach ($searchResults as $movie): ?>
                    <a href="content.php?id=<?php echo $movie['id']; ?>" style="text-decoration:none; color:white;">
                        <div class="card">
                            <img src="https://image.tmdb.org/t/p/w300<?php echo $movie['poster_path']; ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                            <p><?php echo htmlspecialchars($movie['title']); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <?php foreach ($moviesByCategory as $category => $movies): ?>
            <h2 class="row-title"><?php echo ucwords(str_replace('_', ' ', $category)); ?></h2>
            <div class="carousel">
                <?php foreach ($movies as $movie): ?>
                    <a href="content.php?id=<?php echo $movie['id']; ?>" style="text-decoration:none; color:white;">
                        <div class="card">
                            <img src="https://image.tmdb.org/t/p/w300<?php echo $movie['poster_path']; ?>" alt="<?php echo htmlspecialchars($movie['title']); ?>">
                            <p><?php echo htmlspecialchars($movie['title']); ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>