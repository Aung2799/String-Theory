<?php
session_start();
include "../includes/db.php";
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

function getTopSongsByArtist($artistName, $max = 2, $cacheDuration = 86400)
{
    $cacheFile = __DIR__ . "/../public/assets/data/cache_" . md5($artistName) . ".json";

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheDuration) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    $apiKey = "API_KEY";
    $url = "https://api.genius.com/search?q=" . urlencode($artistName);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $songs = [];

    if (isset($data['response']['hits'])) {
        foreach ($data['response']['hits'] as $hit) {
            $songs[] = $hit;
            if (count($songs) >= $max) break;
        }
    }

    file_put_contents($cacheFile, json_encode($songs));

    return $songs;
}

$popularArtists = ['Ed Sheeran', 'John Mayer', 'Taylor Swift', 'James Blunt', 'Passenger'];
$popularSongs = [];
foreach ($popularArtists as $artist) {
    $popularSongs = array_merge($popularSongs, getTopSongsByArtist($artist, 2));
}

$localSongs = [];
$localQuery = "SELECT * FROM songs WHERE song_api_id IS NULL ORDER BY created_at DESC LIMIT 10";
$localResult = $conn->query($localQuery);
if ($localResult && $localResult->num_rows > 0) {
    while ($row = $localResult->fetch_assoc()) {
        $localSongs[] = $row;
    }
}

$searchPerformed = isset($_GET['search']);
$songResults = [];

if ($searchPerformed) {
    $searchQuery = $_GET['search'];
    $songResults = getTopSongsByArtist($searchQuery, 10);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>String Theory</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/assets/css/page_index.css">
</head>

<body>
    <div class="container">
        <h1>
            Welcome back<?= isset($_SESSION['username']) ? ', ' . htmlspecialchars($_SESSION['username']) : '' ?> to String Theory
        </h1>

        <form action="indexx.php" method="GET">
            <input type="text" name="search" placeholder="Search for a song..." required>
            <button type="submit">Search</button>
        </form>

        <?php if ($searchPerformed && empty($songResults)) : ?>
            <p>No results found for "<?= htmlspecialchars($_GET['search']) ?>". Try a different song.</p>
        <?php endif; ?>

        <?php
        $displaySongs = $searchPerformed ? $songResults : $popularSongs;
        if (!empty($displaySongs)) : ?>
            <h2><?= $searchPerformed ? "Search Results from Genius" : "Popular Guitar Songs" ?></h2>
            <ul class="song-list">
                <?php foreach ($displaySongs as $hit) :
                    $songId = $hit['result']['id'];
                    $title = htmlspecialchars($hit['result']['title']);
                    $artist = htmlspecialchars($hit['result']['primary_artist']['name']);
                    $imageUrl = htmlspecialchars($hit['result']['song_art_image_thumbnail_url']);
                ?>
                    <li class="song-item">
                        <div class="song-details">
                            <img src="<?= $imageUrl ?>" alt="<?= $title ?>" />
                            <a class="song-title" href="song_details.php?id=<?= $songId ?>&title=<?= urlencode($title) ?>&artist=<?= urlencode($artist) ?>">
                                <?= $title ?> - <?= $artist ?>
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if (!empty($localSongs)) : ?>
            <h2>Manually Added Songs</h2>
            <ul class="song-list">
                <?php foreach ($localSongs as $song) : ?>
                    <li class="song-item">
                        <div class="song-details">
                            <?php if (!empty($song['album_art'])): ?>
                                <img src="../public/assets/images/<?= htmlspecialchars($song['album_art']) ?>" alt="<?= htmlspecialchars($song['title']) ?>">
                            <?php endif; ?>
                            <a class="song-title" href="song_details_local.php?id=<?= $song['id'] ?>&title=<?= urlencode($song['title']) ?>&artist=<?= urlencode($song['artist']) ?>">
                                <?= htmlspecialchars($song['title']) ?> - <?= htmlspecialchars($song['artist']) ?>
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>

</html>
