<?php
session_start();
include "../includes/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $favorite_id = $_POST['favorite_id'] ?? null;

    if (!$favorite_id) {
        die("Missing favorite ID.");
    }

    $user_id = $_SESSION['user_id'];
    $checkStmt = $conn->prepare("SELECT id FROM favorites WHERE id = ? AND user_id = ?");
    $checkStmt->bind_param("ii", $favorite_id, $user_id);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows === 1) {
        $deleteStmt = $conn->prepare("DELETE FROM favorites WHERE id = ?");
        $deleteStmt->bind_param("i", $favorite_id);
        $deleteStmt->execute();
        $deleteStmt->close();
    }

    $checkStmt->close();
}

header("Location: favorites.php");
exit;