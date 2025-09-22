<?php
// Check if the URL is provided
if (!isset($_GET['url']) || empty($_GET['url'])) {
    http_response_code(400);
    echo "Missing URL.";
    exit;
}

$url = urldecode($_GET['url']);
$escapedUrl = escapeshellarg($url);

// Full path to your Node.js script
$nodeScript = __DIR__ . "/scrape_chords.js";

// Build command to run the Node.js scraper with the provided URL
$command = "node " . escapeshellarg($nodeScript) . " $escapedUrl";

// Execute the command and capture output
$output = shell_exec($command);

// If something came back, show it; otherwise, show an error message
if ($output) {
    echo nl2br(htmlspecialchars($output));
} else {
    echo "<span style='color:red;'>No chords found or failed to scrape.</span>";
}
