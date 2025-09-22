<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<link rel="stylesheet" href="../public/assets/css/navbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<nav class="navbar">
    <div class="logo">String Theory</div>
    <div class="menu-icon" onclick="toggleNav()">
        <i class="fas fa-bars"></i>
    </div>
    <ul class="nav-links" id="navLinks">
        <li>
            <button id="toggleMode" class="mode-toggle" title="Toggle light/dark mode">
                <i class="fas fa-moon"></i>
            </button>
        </li>
        <li><a href="home.php" class="<?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : '' ?>">Home</a></li>
        <li><a href="indexx.php" class="<?= basename($_SERVER['PHP_SELF']) == 'indexx.php' ? 'active' : '' ?>">Songs</a></li>
        <li><a href="artists.php" class="<?= basename($_SERVER['PHP_SELF']) == 'artists.php' ? 'active' : '' ?>">Artists</a></li>
        <li><a href="favorites.php" class="<?= basename($_SERVER['PHP_SELF']) == 'favorites.php' ? 'active' : '' ?>">Favorites</a></li>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>

<script>
function toggleNav() {
    document.getElementById("navLinks").classList.toggle("show");
}

document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.getElementById("toggleMode");
    const icon = toggleBtn.querySelector("i");
    const body = document.body;

    const savedTheme = localStorage.getItem("theme") || "dark";
    body.classList.toggle("light-mode", savedTheme === "light");
    icon.classList.replace("fa-moon", savedTheme === "light" ? "fa-sun" : "fa-moon");

    toggleBtn.addEventListener("click", () => {
        const isLight = body.classList.contains("light-mode");

        body.classList.toggle("light-mode", !isLight);
        icon.classList.replace(isLight ? "fa-sun" : "fa-moon", isLight ? "fa-moon" : "fa-sun");
        localStorage.setItem("theme", isLight ? "dark" : "light");
    });
});
</script>
