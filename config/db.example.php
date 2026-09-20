<?php
// Copy this file to db.php and fill in your own Supabase credentials

$host = "YOUR_SUPABASE_HOST";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "YOUR_DB_PASSWORD";

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
