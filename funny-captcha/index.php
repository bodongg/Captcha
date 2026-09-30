<?php
declare(strict_types=1);
session_save_path(sys_get_temp_dir());
session_start();
require __DIR__ . '/lib/guess.php';
if (empty($_SESSION['demo_logged_in'])) {
    header('Location: login.php');
    exit;
}
if (!empty($_SESSION['puzzle_verified']) && (int) ($_SESSION['guess_round'] ?? 0) >= count(guess_rounds())) {
    header('Location: home.php');
    exit;
}

$_SESSION['puzzle_round'] = 0;
$_SESSION['puzzle_verified'] = false;
$_SESSION['guess_round'] = 0;
$token = $_SESSION['csrf_token'] ??= bin2hex(random_bytes(16));

$puzzles = [
    ['name' => 'Elton', 'image' => 'assets/images/elton.jpg', 'columns' => 5, 'rows' => 2],
    ['name' => 'Jovarri', 'image' => 'assets/images/jovarri.jpg', 'columns' => 2, 'rows' => 5],
    ['name' => 'C&J', 'image' => 'assets/images/c&j.jpg', 'columns' => 5, 'rows' => 2],
];
foreach ($puzzles as &$puzzle) {
    $size = getimagesize(__DIR__ . '/' . $puzzle['image']);
    $puzzle['aspect'] = $size === false ? 1 : $size[0] / $size[1];
}
unset($puzzle);
$puzzleJson = json_encode($puzzles, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
$guessPictures = array_map(static fn (array $round): string => guess_image_url($round['guess']), guess_rounds());
$guessJson = json_encode($guessPictures, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Facebook</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/captcha.css">
  <link rel="stylesheet" href="assets/css/guess.css">
  <script src="assets/js/jigsaw-shape.js" defer></script>
  <script src="assets/js/puzzle.js" defer></script>
  <script src="assets/js/guess.js" defer></script>
</head>
<body class="captcha-page" data-token="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" data-puzzles="<?= htmlspecialchars($puzzleJson ?: '[]', ENT_QUOTES, 'UTF-8') ?>" data-guess-pictures="<?= htmlspecialchars($guessJson ?: '[]', ENT_QUOTES, 'UTF-8') ?>">
  <main class="logged-in-page">
    <header class="logged-in-header"><span class="small-brand">f</span><strong>FacePuzzle</strong><form method="post" action="login.php"><input type="hidden" name="action" value="logout"><button class="logout-button" type="submit">Log out</button></form></header>
    <div class="logged-in-copy"><p>WELCOME BACK</p><h1>One small check before you continue.</h1><span>We have a few questions about whether you are human.</span></div>
  </main>

  <div class="captcha-backdrop">
    <section class="captcha-card" role="dialog" aria-modal="true" aria-labelledby="captcha-title">
      <header class="captcha-heading"><span class="captcha-eyebrow">FACEPUZZLE VERIFICATION</span><h2 id="captcha-title">Complete the picture</h2><p>Drag the scattered pieces into the correct places.</p></header>
      <div class="captcha-content">
        <div class="challenge-meta"><span id="photo-number">Picture 1 of 3</span><span id="piece-count">0 / 10 pieces</span></div>
        <div class="puzzle-columns">
          <div class="puzzle-area"><p class="area-title">PICTURE</p><div id="puzzle-board" class="puzzle-board" aria-label="Empty puzzle board"></div></div>
          <div class="puzzle-area"><p class="area-title">SCATTERED PIECES</p><div id="piece-tray" class="piece-tray" aria-label="Scattered puzzle pieces"></div></div>
        </div>
        <p id="puzzle-status" class="puzzle-status" role="status" aria-live="polite">Click a piece, then click its matching space. Drag and drop also works.</p>
        <div id="human-check-wrap" class="human-check-wrap" hidden><label class="human-check"><input id="puzzle-human" type="checkbox"><span>I'm not a robot</span></label><img class="check-logo" src="assets/images/recaptcha-logo.png" alt="" aria-hidden="true"></div>
      </div>
      <footer class="captcha-footer"><button id="reset-puzzle" class="icon-action" type="button" aria-label="Shuffle pieces again" title="Shuffle pieces again">↻</button><span>Class demo CAPTCHA</span><button id="puzzle-verify" class="captcha-verify" type="button">Next picture</button></footer>
    </section>
  </div>

  <div id="guess-captcha" class="guess-backdrop" hidden>
    <section class="guess-card" role="dialog" aria-modal="true" aria-labelledby="guess-title">
      <header class="captcha-heading"><span class="captcha-eyebrow">FACEPUZZLE VERIFICATION · ROUND TWO</span><h2 id="guess-title" tabindex="-1">Guess the picture</h2><p>Five pictures. Five guesses. One very suspicious CAPTCHA.</p></header>
      <div class="guess-content">
        <div class="guess-meta"><span id="guess-number">Picture 1 of 5</span><span>Guess who's hiding</span></div>
        <div id="guess-photo-frame" class="guess-photo-frame"><div class="guess-photo-inner"><img id="guess-photo" class="guess-photo-front" src="<?= htmlspecialchars($guessPictures[0], ENT_QUOTES, 'UTF-8') ?>" alt="Mystery picture 1"><img id="guess-photo-reveal" class="guess-photo-back" alt=""></div></div>
        <form id="guess-form" novalidate>
          <label class="guess-label" for="guess-answer">Who or what is in the picture?</label>
          <input id="guess-answer" class="guess-input" type="text" autocomplete="off" spellcheck="false" maxlength="80" placeholder="Type your guess here">
          <div class="human-check-wrap guess-human-wrap"><label class="human-check"><input id="guess-human" type="checkbox"><span>I'm not a robot</span></label><img class="check-logo" src="assets/images/recaptcha-logo.png" alt="" aria-hidden="true"></div>
          <p id="guess-status" class="guess-status" role="status" aria-live="polite">Look closely, then make your guess.</p>
          <button id="guess-verify" class="guess-verify" type="submit">Verify</button>
        </form>
        <div id="guess-reveal" class="guess-reveal" hidden><p id="guess-result" role="status"></p><button id="guess-next" class="guess-verify" type="button">Next picture →</button></div>
      </div>
      <footer class="guess-footer">Class demo CAPTCHA · No real robots were consulted</footer>
    </section>
  </div>
</body>
</html>
