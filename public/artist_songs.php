<?php
session_start();
include '../includes/db.php';
include 'navbar.php';

function fetchSongsByArtist($artist, $limit = 20)
{
    $apiKey = "API_KEY";
    $songs = [];
    $page = 1;
    $collected = 0;

    while ($collected < $limit) {
        $apiUrl = "https://api.genius.com/search?q=" . urlencode($artist) . "&page=$page";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $apiKey"]);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) break;
        $data = json_decode($response, true);
        if (!isset($data['response']['hits']) || empty($data['response']['hits'])) break;

        foreach ($data['response']['hits'] as $hit) {
            $songs[] = [
                'song_title' => $hit['result']['title'],
                'song_id' => $hit['result']['id'],
                'image' => $hit['result']['song_art_image_thumbnail_url'] ?? 'assets/img/music-note.png',
                'popularity' => $hit['result']['stats']['pageviews'] ?? 0
            ];
            $collected++;
            if ($collected >= $limit) break;
        }
        $page++;
        if ($page > 10) break;
    }

    usort($songs, fn($a, $b) => $b['popularity'] - $a['popularity']);
    return $songs;
}

$artist = $_GET['artist'] ?? 'Unknown';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
$songs = fetchSongsByArtist($artist, $limit);
$nextLimit = $limit + 20;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Songs by <?= htmlspecialchars($artist) ?></title>
    <link rel="stylesheet" href="../public/assets/css/artist_songs.css">
</head>

<body>
    <div class="container">
        <h1>Songs by <?= htmlspecialchars($artist) ?></h1>
        <ul class="song-list">
            <?php if (!empty($songs)) : ?>
                <?php foreach ($songs as $song) : ?>
                    <li class="song-item">
                        <div class="song-details">
                            <img src="<?= htmlspecialchars($song['image']) ?>" alt="Album Art">
                            <a class="song-title" href="song_details.php?id=<?= $song['song_id'] ?>&title=<?= urlencode($song['song_title']) ?>&artist=<?= urlencode($artist) ?>">
                                <?= htmlspecialchars($song['song_title']) ?>
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else : ?>
                <li style="color: gray;">No songs found for this artist.</li>
            <?php endif; ?>
        </ul>

        <div class="load-more">
            <a href="?artist=<?= urlencode($artist) ?>&limit=<?= $nextLimit ?>">Load More</a>
        </div>
    </div>
</body>

</html>
