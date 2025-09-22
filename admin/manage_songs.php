<?php
session_start();
include "../includes/db.php";
include 'admin_auth.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$query = "SELECT * FROM songs WHERE (title LIKE ? OR artist LIKE ?) AND lyrics != '' AND chords != '' ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$search_param = "%$search%";
$stmt->bind_param("ss", $search_param, $search_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Songs</title>
    <link rel="stylesheet" href="../public/assets/css/manage_songs.css">
</head>

<body>
    <div class="container">
        <h1>Manage Songs</h1>

        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search songs or artist..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Search</button>
        </form>

        <div class="admin-actions">
            <a href="add_chord.php" class="btn">➕ Add New Song</a>
            <a href="dashboard.php" class="btn">⬅️ Back to Dashboard</a>
        </div>

        <table class="styled-table">
            <thead>
                <tr>
                    <th>Album Art</th>
                    <th>Title</th>
                    <th>Artist</th>
                    <th>Chords</th>
                    <th>Lyrics</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php if (!empty($row['album_art'])): ?>
                                    <img src="../public/assets/images/<?= htmlspecialchars($row['album_art']) ?>" width="40">
                                <?php else: ?>
                                    <span style="color: gray;">No image</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['artist']) ?></td>
                            <td><?= strlen($row['chords']) > 100 ? substr($row['chords'], 0, 100) . '...' : $row['chords']; ?></td>
                            <td><?= strlen($row['lyrics']) > 100 ? substr($row['lyrics'], 0, 100) . '...' : $row['lyrics']; ?></td>

                            <td class="action-buttons">
                                <a href="edit_songs.php?id=<?= $row['id'] ?>">✏️ Edit</a>
                                <a href="delete_songs.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">🗑️ Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No songs found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>

</html>