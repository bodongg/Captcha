<?php
declare(strict_types=1);
session_save_path(sys_get_temp_dir());
session_start();
require __DIR__ . '/lib/auth.php';

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['action'] ?? '') === 'logout') {
        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: login.php');
        exit;
    }
    $email = (string) ($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    if (valid_demo_login($email, $password)) {
        session_regenerate_id(true);
        $_SESSION['demo_logged_in'] = true;
        $_SESSION['puzzle_round'] = 0;
        $_SESSION['puzzle_verified'] = false;
        $_SESSION['guess_round'] = 0;
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        header('Location: index.php');
        exit;
    }
    $error = 'That email or password is incorrect. Try the class demo credentials.';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Facebook</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
  <main class="login-layout">
    <section class="login-story" aria-label="Class demo introduction">
      <div class="brand-mark" aria-hidden="true">f</div>
      <div class="story-art" aria-hidden="true">
        <div class="art-card art-card-back"><span class="art-sun"></span><span class="art-hill"></span></div>
        <div class="art-card art-card-front"><span class="art-puzzle">?</span><span class="art-caption">Are you human?</span></div>
        <div class="art-bubble art-bubble-one">10</div>
        <div class="art-bubble art-bubble-two">✓</div>
      </div>
      <h1>Explore the<br>things <span>you love.</span></h1>
      <p>A very serious login for a very unserious CAPTCHA.</p>
    </section>

    <section class="login-panel" aria-labelledby="login-heading">
      <div class="login-panel-inner">
        <p class="demo-label">Facebook Class Demo</p>
          <h2 id="login-heading">Log in to Facebook</h2>
          <p class="login-intro">Enter the class credentials to begin your human verification.</p>
          <?php if ($error !== ''): ?>
            <p class="form-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
          <?php endif; ?>
          <form method="post" action="login.php" class="login-form">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" autocomplete="username" placeholder="Email address" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>
            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Password" required>
            <button type="submit" class="primary-button">Log in</button>
          </form>
          <p class="login-footnote">For the class activity only. Do not enter a real account password.</p>
      </div>
    </section>
  </main>
</body>
</html>
