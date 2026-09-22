<?php
// api/fetch-tmdb.php
require '../config/db.php';
require '../config/tmdb-api-key.php';

set_time_limit(0);
ob_implicit_flush(true);
ob_end_flush();

$apiKey = TMDB_API_KEY;

function fetchTMDB($url, $retries = 5) {
    for ($i = 0; $i < $retries; $i++) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($response === false) {
            echo "cURL error (attempt " . ($i+1) . "): " . curl_error($ch) . "<br>";
            flush();
        }

        curl_close($ch);

        if ($response !== false && $httpCode === 200) {
            return json_decode($response, true);
        }
        usleep(500000);
    }
    return null;
}

$genreData = fetchTMDB("https://api.themoviedb.org/3/genre/movie/list?api_key=$apiKey");
$genreMap = [];
if ($genreData && isset($genreData['genres'])) {
    foreach ($genreData['genres'] as $genre) {
        $genreMap[$genre['id']] = $genre['name'];
    }
}

$categories = [
    "trending" => "https://api.themoviedb.org/3/trending/movie/week?api_key=$apiKey",
    "popular"  => "https://api.themoviedb.org/3/movie/popular?api_key=$apiKey",
    "top_rated" => "https://api.themoviedb.org/3/movie/top_rated?api_key=$apiKey",
];

foreach ($categories as $categoryName => $url) {
    $data = fetchTMDB($url);

    if (!$data || !isset($data['results'])) {
        echo "Failed to fetch $categoryName, skipping.<br>";
        ob_flush(); flush();
        continue;
    }

    foreach ($data['results'] as $movie) {
        $tmdbId = $movie['id'];
        $title = $movie['title'];
        $poster = $movie['poster_path'];
        $overview = $movie['overview'];
        $releaseDate = $movie['release_date'] ?: null;
        $tmdbRating = $movie['vote_average'] ?? null;

        $genreNames = [];
        if (!empty($movie['genre_ids'])) {
            foreach ($movie['genre_ids'] as $gid) {
                if (isset($genreMap[$gid])) {
                    $genreNames[] = $genreMap[$gid];
                }
            }
        }
        $genres = implode(", ", $genreNames);

        $creditsData = fetchTMDB("https://api.themoviedb.org/3/movie/$tmdbId/credits?api_key=$apiKey");

        $castArray = [];
        $director = "";
        if ($creditsData) {
            if (!empty($creditsData['cast'])) {
                $topCast = array_slice($creditsData['cast'], 0, 5);
                foreach ($topCast as $actor) {
                    $castArray[] = [
                        'name' => $actor['name'],
                        'character' => $actor['character'] ?? '',
                        'photo' => $actor['profile_path'] ?? null,
                    ];
                }
            }
            if (!empty($creditsData['crew'])) {
                foreach ($creditsData['crew'] as $person) {
                    if ($person['job'] === 'Director') {
                        $director = $person['name'];
                        break;
                    }
                }
            }
        }
        $cast = json_encode($castArray);

        try {
            $check = $pdo->prepare("SELECT id FROM movies WHERE tmdb_id = ? AND category = ?");
            $check->execute([$tmdbId, $categoryName]);
            $existing = $check->fetch();

            if (!$existing) {
                $insert = $pdo->prepare('INSERT INTO movies (tmdb_id, title, poster_path, overview, category, release_date, genres, "cast", director, tmdb_rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $insert->execute([$tmdbId, $title, $poster, $overview, $categoryName, $releaseDate, $genres, $cast, $director, $tmdbRating]);
            } else {
                $update = $pdo->prepare('UPDATE movies SET genres = ?, "cast" = ?, director = ?, tmdb_rating = ? WHERE id = ?');
                $update->execute([$genres, $cast, $director, $tmdbRating, $existing['id']]);
            }
            echo "Processed: $title (genres: $genres)<br>";
        } catch (PDOException $e) {
            echo "DB ERROR on $title: " . $e->getMessage() . "<br>";
        }

        //ob_flush();
        flush();
    }
}

echo "<br><strong>Done!</strong>";
?>