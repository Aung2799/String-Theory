<?php
session_start();
include "../includes/db.php";
include 'admin_auth.php';

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $artist = trim($_POST['artist']);
    $chords = trim($_POST['chords']);
    $lyrics = trim($_POST['lyrics']);

    if (empty($title) || empty($artist) || empty($chords) || empty($lyrics)) {
        $error = "❌ All fields are required. Please fill in all fields.";
    } else {
        $album_art = "";
        if (!empty($_FILES['album_art']['name'])) {
            $target_dir = "../public/assets/images/";
            $album_art = basename($_FILES["album_art"]["name"]);
            $target_file = $target_dir . $album_art;
            move_uploaded_file($_FILES["album_art"]["tmp_name"], $target_file);
        }

        $sql = "INSERT INTO songs (title, artist, chords, lyrics, album_art, added_by) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $added_by = 'admin';
        $stmt->bind_param("ssssss", $title, $artist, $chords, $lyrics, $album_art, $added_by);

        if ($stmt->execute()) {
            $success = "✅ Song added successfully!";
        } else {
            $error = "❌ Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Song</title>
    <link rel="stylesheet" href="../public/assets/css/add_chord.css">
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
        <h1>Add New Song</h1>

        <?php if ($success): ?>
            <p style="color: green; font-weight: bold;"><?= $success ?></p>
        <?php elseif ($error): ?>
            <p style="color: red; font-weight: bold;"><?= $error ?></p>
        <?php endif; ?>

        <form name="songForm" action="" method="POST" enctype="multipart/form-data" onsubmit="return validateSongForm()">
            <label for="title">Song Title:</label>
            <input type="text" name="title" required>

            <label for="artist">Artist:</label>
            <input type="text" name="artist" required>

            <label for="album_art">Album Art (Optional):</label>
            <input type="file" name="album_art">

            <label for="chords">Chords (one line of chords per line of lyrics):</label>
            <textarea name="chords" rows="12" cols="80" style="white-space: pre-wrap;" required></textarea>

            <label for="lyrics">Lyrics:</label>
            <textarea name="lyrics" rows="12" cols="80" style="white-space: pre-wrap;" required></textarea>

            <button type="submit">Add Song</button>
        </form>

        <a href="dashboard.php">Back to Dashboard</a>
    </div>
</body>

</html>
