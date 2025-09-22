<?php
session_start();
include "../includes/db.php";
include 'navbar.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Function to fetch popular artists from the Genius API (first page only)
function fetchPopularArtists()
{
    $apiKey = "OGY502KwO5C7WGd72u7ngZBETuqf1wrs9QFZtp0ghGmb3vADuI3TEHDxuFMJCVcu";
    $apiUrl = "https://api.genius.com/search?q=guitar&per_page=30";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $artists = [];

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['response']['hits'])) {
            foreach ($data['response']['hits'] as $hit) {
                $artistName = $hit['result']['primary_artist']['name'];
                $artistImage = $hit['result']['primary_artist']['image_url'] ?? null;

                if (!array_key_exists($artistName, $artists)) {
                    $artists[$artistName] = $artistImage;
                }
            }
        }
    }

    return $artists;
}

$artists = fetchPopularArtists();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist List</title>
    <link rel="stylesheet" href="../public/assets/css/artists.css">
</head>

<body>

    <div class="container">
        <h1 style="text-align: center">Popular Guitar Artists</h1>

        <form action="artist_songs.php" method="GET" class="search-container">
            <input type="text" name="artist" placeholder="Enter artist name..." required>
            <button type="submit">Search</button>
        </form>

        <ul id="artistList">
            <?php foreach ($artists as $name => $image): ?>
                <li>
                    <a href="artist_songs.php?artist=<?= urlencode($name) ?>">
                        <?php if ($image): ?>
                            <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($name) ?>" class="artist-img">
                        <?php endif; ?>
                        <?= htmlspecialchars($name) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>

</body>

</html>