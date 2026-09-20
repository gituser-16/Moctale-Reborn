<?php

// api/fetch-tmdb.php
require '../config/db.php';
require '../config/tmdb-api-key.php';

$apiKey = TMDB_API_KEY;


// Categories we'll fetch — mapped to TMDB endpoints
$categories = [
    "trending" => "https://api.themoviedb.org/3/trending/movie/week?api_key=$apiKey",
    "popular"  => "https://api.themoviedb.org/3/movie/popular?api_key=$apiKey",
    "top_rated" => "https://api.themoviedb.org/3/movie/top_rated?api_key=$apiKey",
];

foreach ($categories as $categoryName => $url) {
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (!isset($data['results'])) continue;

    foreach ($data['results'] as $movie) {
        $tmdbId = $movie['id'];
        $title = $movie['title'];
        $poster = $movie['poster_path'];
        $overview = $movie['overview'];
        $releaseDate = $movie['release_date'] ?: null;

        // Avoid duplicate inserts: check if this tmdb_id + category already exists
        $check = $pdo->prepare("SELECT id FROM movies WHERE tmdb_id = ? AND category = ?");
        $check->execute([$tmdbId, $categoryName]);

        if (!$check->fetch()) {
            $insert = $pdo->prepare("INSERT INTO movies (tmdb_id, title, poster_path, overview, category, release_date) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->execute([$tmdbId, $title, $poster, $overview, $categoryName, $releaseDate]);
        }
    }
}

echo "Movies fetched and saved successfully!";
?>