<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>String Theory</title>
    <link rel="stylesheet" href="../public/assets/css/navbar.css">
    <link rel="stylesheet" href="../public/assets/css/home_page.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <?php include 'navbar.php'; ?>
    <div class="hero">
        <h1>Welcome to String Theory</h1>
        <p class="tagline" id="rotating-tagline"></p>
        <p>Whether you're strumming alone or jamming with friends, String Theory is your ultimate sidekick. Explore accurate chords, synced lyrics, smart chord shifting tools, and build your own library of favorite songs — all in one place made just for guitar lovers.</p>
        <div class="cta-buttons">
            <a href="indexx.php" class="cta-button">
                <i class="fas fa-music"></i>
                <span>Browse Songs</span>
            </a>
            <a href="artists.php" class="cta-button">
                <i class="fas fa-users"></i>
                <span>Artist List</span>
            </a>
            <a href="favorites.php" class="cta-button">
                <i class="fas fa-heart"></i>
                <span>Favorites</span>
            </a>
        </div>
    </div>

    <div class="section">
        <h2>What You Can Do</h2>
        <div class="features">
            <div class="feature-box">
                <i class="fas fa-music"></i>
                <h3>Explore Chords & Lyrics</h3>
                <p>Search and view song lyrics with properly formatted guitar chords above each line — perfect for playing along.</p>
            </div>
            <div class="feature-box">
                <i class="fas fa-heart"></i>
                <h3>Save Favorites</h3>
                <p>Click the heart icon to save any song to your personal favorites list, so you can find and revisit them easily.</p>
            </div>
            <div class="feature-box">
                <i class="fas fa-random"></i>
                <h3>Chord Progression</h3>
                <p>Automatically adjust chord positions based on your chosen capo fret, making transposing easy and accurate.</p>
            </div>
        </div>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> String Theory. All rights reserved.
    </footer>

    <script>
        const taglines = [
            "The universe of chords, at your fingertips.",
            "Built for players. Tuned for passion.",
            "String Theory — because the universe runs on music.",
            "One theory to play them all."
        ];
        let taglineIndex = 0;
        const taglineEl = document.getElementById("rotating-tagline");

        function rotateTagline() {
            taglineEl.innerText = taglines[taglineIndex];
            taglineIndex = (taglineIndex + 1) % taglines.length;
        }

        rotateTagline();
        setInterval(rotateTagline, 4000);
    </script>
</body>

</html>
