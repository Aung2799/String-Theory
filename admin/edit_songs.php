<?php
session_start();
include "../includes/db.php";
include 'admin_auth.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Invalid song ID.");
}

$success = '';
$error = '';

// Fetch current song data
$stmt = $conn->prepare("SELECT * FROM songs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$song = $result->fetch_assoc();
$stmt->close();

if (!$song) {
    die("Song not found.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST['title']);
    $artist = trim($_POST['artist']);
    $chords = trim($_POST['chords']);
    $lyrics = trim($_POST['lyrics']);
    $album_art = $song['album_art'];

    if (empty($title) || empty($artist) || empty($chords) || empty($lyrics)) {
        $error = "❌ All fields are required. Please fill in all fields.";
    } else {
        if (!empty($_FILES['album_art']['name'])) {
            $target_dir = "../public/assets/images/";
            $album_art = basename($_FILES["album_art"]["name"]);
            $target_file = $target_dir . $album_art;
            move_uploaded_file($_FILES["album_art"]["tmp_name"], $target_file);
        }

        $update = $conn->prepare("UPDATE songs SET title=?, artist=?, chords=?, lyrics=?, album_art=? WHERE id=?");
        $update->bind_param("sssssi", $title, $artist, $chords, $lyrics, $album_art, $id);

        if ($update->execute()) {
            $success = "✅ Song updated successfully.";
            $song = ['title' => $title, 'artist' => $artist, 'chords' => $chords, 'lyrics' => $lyrics, 'album_art' => $album_art];
        } else {
            $error = "❌ Update failed: " . $update->error;
        }
        $update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Song</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
    <script>
        function validateSongForm() {
            const title = document.forms["songForm"]["title"].value.trim();
            const artist = document.forms["songForm"]["artist"].value.trim();
            const chords = document.forms["songForm"]["chords"].value.trim();
            const lyrics = document.forms["songForm"]["lyrics"].value.trim();

            if (!title || !artist || !chords || !lyrics) {
                alert("Please fill in all fields: Title, Artist, Chords, and Lyrics.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Edit Song</h1>

        <?php if ($success): ?>
            <p style="color: green; font-weight: bold;"><?= $success ?></p>
        <?php elseif ($error): ?>
            <p style="color: red; font-weight: bold;"><?= $error ?></p>
        <?php endif; ?>

        <form name="songForm" action="" method="POST" enctype="multipart/form-data" onsubmit="return validateSongForm()">
            <label for="title">Song Title:</label>
            <input type="text" name="title" value="<?= htmlspecialchars($song['title']) ?>" required>

            <label for="artist">Artist:</label>
            <input type="text" name="artist" value="<?= htmlspecialchars($song['artist']) ?>" required>

            <label for="album_art">Album Art:</label>
            <?php if (!empty($song['album_art'])): ?>
                <img src="../public/assets/images/<?= htmlspecialchars($song['album_art']) ?>" width="100" alt="Album Art"><br>
            <?php endif; ?>
            <input type="file" name="album_art">

            <label for="chords">Chords:</label>
            <textarea name="chords" rows="12" cols="80" style="white-space: pre-wrap;" required><?= htmlspecialchars($song['chords']) ?></textarea>

            <label for="lyrics">Lyrics:</label>
            <textarea name="lyrics" rows="12" cols="80" style="white-space: pre-wrap;" required><?= htmlspecialchars($song['lyrics']) ?></textarea>

            <button type="submit">Update Song</button>
        </form>
        <a href="manage_songs.php">← Back to Song Management</a>
    </div>
</body>
</html>