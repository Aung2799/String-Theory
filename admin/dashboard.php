<?php
session_start();
include '../includes/db.php';
include 'admin_auth.php';

$adminName = $_SESSION['admin_username'];
$query = "SELECT title, artist FROM songs WHERE lyrics IS NOT NULL AND chords IS NOT NULL ORDER BY id DESC LIMIT 10";
$result = $conn->query($query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../public/assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>Welcome, <?= htmlspecialchars($_SESSION['admin_username']) ?></h1>
        <p>You are now logged in as an admin.</p>

        <div class="admin-actions">
            <a href="manage_songs.php" class="btn">Manage Songs</a>
        </div>

        <h2>Latest Songs</h2>
        <table class="song-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Artist</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['artist']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="2">No songs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="admin-actions" style="margin-top: 30px;">
            <a href="logout.php" class="btn">Logout</a>
        </div>
    </div>
</body>
</html>
