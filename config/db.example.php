<?php
// Copy this file to db.php and fill in your own Supabase credentials

$host = "db.mviowbyjwsojuhjgmtjw.supabase.co";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "YOUR_TMDB_PASSWORD";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
