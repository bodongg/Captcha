<?php
declare(strict_types=1);
session_save_path(sys_get_temp_dir());
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require __DIR__ . '/lib/puzzle.php';
require __DIR__ . '/lib/guess.php';

function json_reply(int $status, array $body): never
{
    http_response_code($status);
    echo json_encode($body);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_reply(405, ['error' => 'Use POST to verify the puzzle.']);
}
if (empty($_SESSION['demo_logged_in'])) {
    json_reply(401, ['error' => 'Please log in first.']);
}
$input = json_decode(file_get_contents('php://input') ?: '', true);
if (!is_array($input) || !is_string($input['token'] ?? null) || !hash_equals((string) ($_SESSION['csrf_token'] ?? ''), $input['token'])) {
    json_reply(400, ['error' => 'This page has expired. Refresh and try again.']);
}
if (($input['stage'] ?? null) === 'guess') {
    if (empty($_SESSION['puzzle_verified'])) {
        json_reply(409, ['error' => 'Finish the jigsaw puzzle first.']);
    }
    $guessRound = $input['round'] ?? null;
    $rounds = guess_rounds();
    if (!is_int($guessRound) || !isset($rounds[$guessRound]) || $guessRound !== ($_SESSION['guess_round'] ?? 0)) {
        json_reply(409, ['error' => 'That picture is no longer active. Refresh and start again.']);
    }
    if (($input['humanChecked'] ?? false) !== true) {
        json_reply(422, ['error' => 'Check “I’m not a robot” before verifying.']);
    }
    if (!is_string($input['answer'] ?? null) || trim($input['answer']) === '' || strlen($input['answer']) > 80) {
        json_reply(422, ['error' => 'Type your guess before verifying.']);
    }
    if (!guess_is_correct($guessRound, $input['answer'])) {
        json_reply(200, ['correct' => false, 'message' => 'Wrong guess. The robot council is taking notes.']);
    }
    $_SESSION['guess_round'] = $guessRound + 1;
    json_reply(200, [
        'correct' => true,
        'answer' => $rounds[$guessRound]['answer'],
        'reveal' => guess_image_url($rounds[$guessRound]['reveal']),
        'completed' => $_SESSION['guess_round'] === count($rounds),
    ]);
}
$round = $input['round'] ?? null;
if (!is_int($round) || $round !== ($_SESSION['puzzle_round'] ?? 0)) {
    json_reply(409, ['error' => 'That picture is no longer active. Refresh and start again.']);
}
if (!valid_puzzle_placements($input['placements'] ?? null)) {
    json_reply(422, ['error' => 'Put all ten pieces in the correct spots first.']);
}
if ($round === 0 || $round === 1) {
    $_SESSION['puzzle_round'] = $round + 1;
    json_reply(200, ['next' => true]);
}
if ($round === 2) {
    if (($input['humanChecked'] ?? false) !== true) {
        json_reply(422, ['error' => 'Check “I’m not a robot” before verifying.']);
    }
    $_SESSION['puzzle_verified'] = true;
    $_SESSION['puzzle_round'] = 3;
    json_reply(200, ['completed' => true]);
}
json_reply(409, ['error' => 'The puzzle is already complete.']);
