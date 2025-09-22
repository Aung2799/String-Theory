<?php
session_start();
include '../includes/db.php';

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if username already exists
    $checkQuery = "SELECT * FROM admins WHERE username = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $error = "Username already exists. Please choose a different one.";
    } else {
        $insertQuery = "INSERT INTO admins (username, password, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("sss", $username, $password, $email);

        if ($stmt->execute()) {
            $message = "Admin account created successfully!";
        } else {
            $error = "Failed to create admin account.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin Account</title>
    <link rel="stylesheet" href="../public/assets/css/style.css">
</head>
<body>
<div class="login-container">
    <h2>Create Admin Account</h2>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Create Account</button>
    </form>
    <?php if ($message): ?>
        <p style="color: green;"><?= $message ?> <a href="login.php">Login here</a></p>
    <?php elseif ($error): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
</div>
</body>
</html>
