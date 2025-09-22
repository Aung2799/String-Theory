<?php

// Chromatic scale using flats
$chromatic = ['C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B'];

// Supported chord suffixes
$chordTypes = [
    '', 'm', '7', 'm7', 'maj7', '#', '#m7',
    '9', 'm9', 'maj9',
    'sus2', 'sus4', 'dim', 'dim7',
    'aug', 'add9',
    '6', 'm6'
];

// Create chord map with 12 semitone options (0 to 11)
$chordMap = [];
foreach ($chromatic as $note) {
    foreach ($chordTypes as $suffix) {
        $fullChord = $note . $suffix;
        $startIndex = array_search($note, $chromatic);
        $progression = [];

        for ($i = 0; $i < 12; $i++) {
            $nextNote = $chromatic[($startIndex + $i) % 12];
            $progression[] = $nextNote . $suffix;
        }

        $chordMap[$fullChord] = $progression;
    }
}

// Transpose a single chord
function transposeChord($chord, $steps)
{
    global $chordMap;

    if (!isset($chordMap[$chord])) {
        return $chord;
    }

    // Normalize transposition within 0–11
    $steps = ($steps % 12 + 12) % 12;

    return $chordMap[$chord][$steps] ?? $chord;
}

// Transpose a string of chords
function transposeChordsInString($chords, $steps)
{
    $chordsArray = explode(' ', $chords);
    foreach ($chordsArray as $i => $chord) {
        $chordsArray[$i] = transposeChord($chord, $steps);
    }
    return implode(' ', $chordsArray);
}

// Handle AJAX POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $steps = isset($_POST['capoPosition']) ? (int)$_POST['capoPosition'] : 0;
    $chords = $_POST['chords'] ?? '';

    $transposedChords = transposeChordsInString($chords, $steps);

    echo json_encode([
        'transposedChords' => $transposedChords
    ]);
} else {
    echo json_encode([
        'error' => 'Invalid request method'
    ]);
}
