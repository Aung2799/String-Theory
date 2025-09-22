<?php
session_start();
include "../includes/db.php";
include 'navbar.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$song_id = $_GET['id'] ?? null;
if (!$song_id) {
    die("Song not found.");
}

$stmt = $conn->prepare("SELECT * FROM songs WHERE id = ?");
$stmt->bind_param("i", $song_id);
$stmt->execute();
$result = $stmt->get_result();
$song = $result->fetch_assoc();

if (!$song) {
    die("Song not found in database.");
}

function formatChordsAndLyrics($chords, $lyrics)
{
    $chordLines = explode("\n", $chords);
    $lyricLines = explode("\n", $lyrics);
    $output = "";

    $max = max(count($chordLines), count($lyricLines));

    for ($i = 0; $i < $max; $i++) {
        $chordLine = $chordLines[$i] ?? "";
        $lyricLine = $lyricLines[$i] ?? "";

        $formattedChordLine = preg_replace_callback('/\b([A-G][#b]?m?(maj7|sus4|dim)?[0-9]?)\b/', function ($matches) {
            return '<span data-chord="' . $matches[1] . '">' . $matches[1] . '</span>';
        }, htmlspecialchars($chordLine));

        $output .= '<pre class="chord-line">' . $formattedChordLine . "</pre>";
        $output .= '<pre class="lyric-line">' . htmlspecialchars($lyricLine) . "</pre>";
    }

    return $output;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($song['title']) ?> - <?= htmlspecialchars($song['artist']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/assets/css/song.css">
</head>

<body>
    <div class="container">
        <div class="song-info-container">
            <?php if (!empty($song['album_art'])): ?>
                <div class="song-image-container">
                    <img src="../public/assets/images/<?= htmlspecialchars($song['album_art']) ?>" alt="Album Art" class="song-image">
                </div>
            <?php endif; ?>
            <div class="song-details-text">
                <h1 class="title"><?= htmlspecialchars($song['title']) ?></h1>
                <h2 class="artist">by <?= htmlspecialchars($song['artist']) ?></h2>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    $user_id = $_SESSION['user_id'];
                    $song_id = $song['id'];
                    $isFavorite = false;

                    $check = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND local_song_id = ? AND song_type = 'local'");
                    $check->bind_param("ii", $user_id, $song_id);
                    $check->execute();
                    $check->store_result();
                    $isFavorite = $check->num_rows > 0;
                    $check->close();
                    ?>
                    <button
                        class="favorite-btn js-toggle-favorite"
                        data-song-id="<?= $song['id'] ?>"
                        data-title="<?= htmlspecialchars($song['title']) ?>"
                        data-artist="<?= htmlspecialchars($song['artist']) ?>"
                        data-song-type="local"
                        title="Toggle Favorite">
                        <i class="<?= $isFavorite ? 'fas' : 'far' ?> fa-heart"></i>
                    </button>
                <?php endif; ?>

            </div>
        </div>

        <div class="scroll-controls">
            <label for="scrollSpeed">Scroll Speed:</label>
            <select id="scrollSpeed">
                <option value="0">Off</option>
                <option value="0.25">0.25x</option>
                <option value="0.5">0.5x</option>
                <option value="1">1x</option>
                <option value="2">2x</option>
                <option value="3">3x</option>
                <option value="4">4x</option>
            </select>
            <button id="startScroll">Start</button>
            <button id="stopScroll">Stop</button>
        </div>

        <div class="transpose-control">
            <label for="transposeRange">Transpose: <span id="transposeValue">0</span></label>
            <div class="transpose-slider-wrapper">
                <button onclick="adjustTranspose(-1)">-</button>
                <input type="range" id="transposeRange" min="-6" max="6" value="0" step="1">
                <button onclick="adjustTranspose(1)">+</button>
            </div>
        </div>

        <div class="lyrics-container">
            <?= formatChordsAndLyrics($song['chords'], $song['lyrics']) ?>
        </div>

        <a href="indexx.php" class="back-link">Back to Song List</a>
    </div>

    <script>
        let scrollInterval;
        let currentCapo = 0;

        document.getElementById("startScroll").addEventListener("click", function() {
            const speed = parseFloat(document.getElementById("scrollSpeed").value);
            const lyricsContainer = document.querySelector(".lyrics-container");
            if (speed > 0) {
                clearInterval(scrollInterval);
                scrollInterval = setInterval(() => {
                    lyricsContainer.scrollTop += 1;
                }, 50 / speed);
            }
        });

        document.getElementById("stopScroll").addEventListener("click", function() {
            clearInterval(scrollInterval);
        });

        function transposeChord(chord, semitones) {
            const notes = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
            const regex = /^([A-G][b#]?)(.*)$/;
            const match = chord.match(regex);
            if (!match) return chord;
            let index = notes.indexOf(match[1].replace('b', '#'));
            if (index === -1) return chord;
            let newIndex = (index + semitones + 12) % 12;
            return notes[newIndex] + match[2];
        }

        function setCapo(fret) {
            currentCapo = fret;
            const spans = document.querySelectorAll('span[data-chord]');
            spans.forEach(span => {
                const original = span.getAttribute('data-chord');
                span.innerText = transposeChord(original, fret);
            });
        }

        document.getElementById("transposeRange").addEventListener("input", function() {
            const value = parseInt(this.value);
            document.getElementById("transposeValue").innerText = value;
            setCapo(value);
        });

        function adjustTranspose(step) {
            const slider = document.getElementById("transposeRange");
            let newValue = parseInt(slider.value) + step;
            newValue = Math.max(-6, Math.min(6, newValue));
            slider.value = newValue;
            document.getElementById("transposeValue").innerText = newValue;
            setCapo(newValue);
        }
    </script>
    <script src="../public/assets/js/favorites.js"></script>

</body>

</html>