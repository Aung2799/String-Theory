<?php
session_start();
header("Content-Type: application/json");
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$userId = $_SESSION['user_id'];
$songId = $_POST['song_id'] ?? '';
$title = $_POST['title'] ?? '';
$artist = $_POST['artist'] ?? '';
$songType = $_POST['song_type'] ?? '';

if (!$songId || !$title || !$artist || !$songType) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if ($songType === 'genius') {
    // Check if it's already favorited
    $check = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND genius_song_api_id = ?");
    $check->bind_param("is", $userId, $songId);
} else {
    // Assume local song
    $check = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND local_song_id = ?");
    $check->bind_param("ii", $userId, $songId);
}
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // Remove favorite
    if ($songType === 'genius') {
        $delete = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND genius_song_api_id = ?");
        $delete->bind_param("is", $userId, $songId);
    } else {
        $delete = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND local_song_id = ?");
        $delete->bind_param("ii", $userId, $songId);
    }
    $delete->execute();
    echo json_encode(['success' => true, 'action' => 'removed']);
} else {
    // Add favorite
    if ($songType === 'genius') {
        $insert = $conn->prepare("INSERT INTO favorites (user_id, genius_song_api_id, song_type) VALUES (?, ?, 'genius')");
        $insert->bind_param("is", $userId, $songId);
    } else {
        $insert = $conn->prepare("INSERT INTO favorites (user_id, local_song_id, song_type) VALUES (?, ?, 'local')");
        $insert->bind_param("ii", $userId, $songId);
    }
    $insert->execute();
    echo json_encode(['success' => true, 'action' => 'added']);
}