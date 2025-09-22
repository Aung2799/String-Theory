<?php
session_start();
include "../includes/db.php";
include 'navbar.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$songId = $_GET['id'] ?? null;
$title = $_GET['title'] ?? null;
$artist = $_GET['artist'] ?? null;

if (!$songId || !$title || !$artist) {
    echo "<h2 style='text-align: center; color: gray;'>Song not found or missing information.</h2>";
    exit;
}

function fetchSongDetails($songId)
{
    $apiKey = "API_KEY";
    $apiUrl = "https://api.genius.com/songs/" . $songId;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $apiKey"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    $songData = json_decode($response, true);

    return [
        'song_image' => $songData['response']['song']['song_art_image_url'] ?? '',
    ];
}

$songData = fetchSongDetails($songId);
$songImage = $songData['song_image'] ?? '';
function slugify($text)
{
    return strtolower(trim(preg_replace('/[^a-zA-Z0-9-]/', '', str_replace(' ', '-', $text))));
}

$cleanArtist = slugify($artist);
$cleanTitle = slugify($title);
$eChordsUrl = "https://www.e-chords.com/chords/{$cleanArtist}/{$cleanTitle}";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title) ?> - <?= htmlspecialchars($artist) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/assets/css/song.css">
    <script src="../public/assets/js/favorites.js"></script>
</head>

<body>
    <div class="container">
        <div class="song-info-container">
            <?php if ($songImage): ?>
                <div class="song-image-container">
                    <img src="<?= $songImage ?>" alt="<?= htmlspecialchars($title) ?>" class="song-image">
                </div>
            <?php endif; ?>

            <div class="song-details-text">
                <h1 class="title"><?= htmlspecialchars($title) ?></h1>
                <h2 class="artist">by <?= htmlspecialchars($artist) ?></h2>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    $user_id = $_SESSION['user_id'];
                    $isFavorite = false;

                    $check = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND genius_song_api_id = ? AND song_type = 'genius'");
                    $check->bind_param("is", $user_id, $songId);
                    $check->execute();
                    $check->store_result();
                    $isFavorite = $check->num_rows > 0;
                    $check->close();
                    ?>
                    <button
                        class="favorite-btn js-toggle-favorite"
                        data-song-id="<?= htmlspecialchars($songId) ?>"
                        data-title="<?= htmlspecialchars($title) ?>"
                        data-artist="<?= htmlspecialchars($artist) ?>"
                        data-song-type="genius"
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

        <div class="chords-container">
            <h3>Chords:</h3>
            <input type="text" id="chordLink" value="<?= $eChordsUrl ?>" readonly style="width: 80%; padding: 10px; display: none;">
            <div id="chordResult" style="margin-top: 10px;">No chords loaded yet.</div>
        </div>

        <div class="lyrics-container">
            <h3>Lyrics:</h3>
            <pre id="lyrics"></pre>
        </div>

        <a href="indexx.php" class="back-link">Back to Song List</a>
    </div>

    <script>
        let scrollInterval;
        let currentCapo = 0;
        let originalChordText = "";

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

        function loadChords() {
            const url = document.getElementById("chordLink").value.trim();
            const output = document.getElementById("chordResult");
            if (!url) {
                alert("Please enter a valid E-Chords URL.");
                return;
            }
            output.innerHTML = "\ud83c\udfb5 Loading chords...";

            fetch(`fetch_chords.php?url=${encodeURIComponent(url)}`)
                .then(res => res.text())
                .then(data => {
                    let formattedChords = data.replace(/\b([A-G][#b]?(m|maj|dim|aug|sus|add)?[0-9]?7?)\b/g, match => {
                        return `<span data-chord="${match}">${match}</span>`;
                    });
                    const lyricsContainer = document.getElementById("lyrics");
                    lyricsContainer.innerHTML = formattedChords;
                    originalChordText = Array.from(document.querySelectorAll('#lyrics span[data-chord]'))
                        .map(span => span.getAttribute('data-chord')).join(' ');
                    output.innerHTML = "";
                })
                .catch(() => {
                    output.innerHTML = "<span style='color:red;'>Failed to load chords.</span>";
                });
        }

        function setCapo(fret) {
            if (fret === currentCapo) return;
            currentCapo = fret;
            if (!originalChordText) return;

            fetch('chords.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `capoPosition=${fret}&chords=${encodeURIComponent(originalChordText)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.transposedChords) {
                        const transposed = data.transposedChords.split(' ');
                        const chordSpans = document.querySelectorAll('#lyrics span[data-chord]');
                        chordSpans.forEach((span, i) => {
                            span.innerText = transposed[i];
                        });
                    } else {
                        alert('Chord transposition failed.');
                    }
                })
                .catch(err => {
                    console.error("Capo error:", err);
                    alert("Error communicating with chords.php");
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

        window.onload = function() {
            loadChords();
        }
    </script>
</body>

</html>
