<?php
session_start();
include "../includes/db.php";
include 'admin_auth.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Invalid song ID.");
}

//Fetch song to delete album art if needed
$stmt = $conn->prepare("SELECT album_art FROM songs WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$song = $result->fetch_assoc();
$stmt->close();

if (!$song) {
    die("Song not found.");
}

// Delete the album art image file
if (!empty($song['album_art'])) {
    $imagePath = "../public/assets/images/" . $song['album_art'];
    if (file_exists($imagePath)) {
        unlink($imagePath);
    }
}

// Delete the song record
$delete = $conn->prepare("DELETE FROM songs WHERE id = ?");
$delete->bind_param("i", $id);
$delete->execute();
$delete->close();

header("Location: dashboard.php");
exit;
?>
