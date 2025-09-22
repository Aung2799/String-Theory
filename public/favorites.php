<?php
session_start();
include "../includes/db.php";
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$apiKey = "API_KEY";

$sql = "SELECT f.id AS favorite_id, f.song_type, f.genius_song_api_id, f.local_song_id,
        s.title AS local_title, s.artist AS local_artist, s.album_art
        FROM favorites f
        LEFT JOIN songs s ON f.local_song_id = s.id AND f.song_type = 'local'
        WHERE f.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorite Songs</title>
    <link rel="stylesheet" href="assets/css/favorites.css">
</head>

<body>
    <div class="container">
        <h1>My Favorite Songs</h1>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                if ($row['song_type'] === 'local') {
                    $songUrl = "song_details_local.php?id=" . urlencode($row['local_song_id']) .
                        "&title=" . urlencode($row['local_title']) .
                        "&artist=" . urlencode($row['local_artist']);
                    $songImage = $row['album_art'] ? "../public/assets/images/" . htmlspecialchars($row['album_art']) : '';
                    $title = $row['local_title'];
                    $artist = $row['local_artist'];
                } else {
                    $title = $row['genius_title'] ?? '';
                    $artist = $row['genius_artist'] ?? '';
                    $imageUrl = "assets/img/music-note.png";

                    if (empty($title) || empty($artist)) {
                        $songId = $row['genius_song_api_id'];
                        $apiUrl = "https://api.genius.com/songs/$songId";

                        $ch = curl_init($apiUrl);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $apiKey"]);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $response = curl_exec($ch);
                        curl_close($ch);

                        $data = json_decode($response, true);
                        $title = $data['response']['song']['title'] ?? 'Unknown Title';
                        $artist = $data['response']['song']['primary_artist']['name'] ?? 'Unknown Artist';
                        $imageUrl = $data['response']['song']['song_art_image_thumbnail_url'] ?? $imageUrl;
                    }

                    $songUrl = "song_details.php?id=" . urlencode($row['genius_song_api_id']) .
                        "&title=" . urlencode($title) .
                        "&artist=" . urlencode($artist);
                    $songImage = $imageUrl;
                }
                ?>

                <div class="song-card">
                    <div class="song-details">
                        <?php if (!empty($songImage)): ?>
                            <img src="<?= $songImage ?>" alt="Song Image" class="song-thumb">
                        <?php endif; ?>
                        <a href="<?= $songUrl ?>" class="song-title">
                            <span><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($artist) ?></span>
                        </a>
                    </div>
                    <form method="POST" action="remove_favorite.php">
                        <input type="hidden" name="favorite_id" value="<?= $row['favorite_id'] ?>">
                        <button type="submit" class="remove-btn">&#10060;</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="margin-top: 20px; color: gray;">No favorite songs found.</p>
        <?php endif; ?>
    </div>
</body>

</html>
