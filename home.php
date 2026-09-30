<?php
declare(strict_types=1);
session_save_path(sys_get_temp_dir());
session_start();
require __DIR__ . '/lib/guess.php';

if (empty($_SESSION['demo_logged_in'])) {
    header('Location: login.php');
    exit;
}
if (empty($_SESSION['puzzle_verified']) || (int) ($_SESSION['guess_round'] ?? 0) < count(guess_rounds())) {
    header('Location: index.php');
    exit;
}
header('Cache-Control: no-store');

$stories = [
    ['title' => 'The pig mystery', 'image' => 'pig.jpg', 'color' => 'amber'],
    ['title' => 'Puzzle survivors', 'image' => 'elton.jpg', 'color' => 'blue'],
    ['title' => 'The final reveal', 'image' => 'james.jpg', 'color' => 'violet'],
    ['title' => 'Robot council', 'image' => 'rene.jpg', 'color' => 'green'],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Home · FacePuzzle class demo</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/home.css">
</head>
<body class="home-page" data-demo-home>
  <svg class="icon-library" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <symbol id="i-search" viewBox="0 0 24 24"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></symbol>
    <symbol id="i-home" viewBox="0 0 24 24"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/><path d="M9 21v-7h6v7"/></symbol>
    <symbol id="i-play" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="16" rx="3"/><path d="m10 8 6 4-6 4zM7 4v4m10-4v4"/></symbol>
    <symbol id="i-people" viewBox="0 0 24 24"><circle cx="9" cy="8" r="3"/><path d="M3 20v-2a6 6 0 0 1 12 0v2zm13-14a3 3 0 0 1 0 6m1 3a5 5 0 0 1 4 5"/></symbol>
    <symbol id="i-grid" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></symbol>
    <symbol id="i-bell" viewBox="0 0 24 24"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 8-3 9h18c0-1-3-2-3-9zM10 21h4"/></symbol>
    <symbol id="i-check" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="m7 12 3.5 3.5L17 9"/></symbol>
    <symbol id="i-like" viewBox="0 0 24 24"><path d="M3 10h4v11H3zM7 11l5-8c2-2 3 0 2 3l-1 3h6c2 0 3 1 2 3l-2 7c-.3 1-1 2-3 2H7z"/></symbol>
    <symbol id="i-comment" viewBox="0 0 24 24"><path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 9 9 0 0 1-3.5-.7L3 21l1.7-5A8.5 8.5 0 1 1 21 11.5z"/></symbol>
    <symbol id="i-share" viewBox="0 0 24 24"><path d="M14 4 21 11l-7 7v-4C8 14 5 16 3 21c0-9 3-13 11-13z"/></symbol>
    <symbol id="i-photo" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="18" rx="2"/><circle cx="8" cy="9" r="2"/><path d="m3 19 6-6 4 3 4-5 4 5"/></symbol>
    <symbol id="i-gift" viewBox="0 0 24 24"><rect x="3" y="9" width="18" height="12" rx="1"/><path d="M2 9h20M12 9v12M7 9C3 9 4 3 7 3c3 0 5 6 5 6s2-6 5-6 4 6 0 6"/></symbol>
    <symbol id="i-logout" viewBox="0 0 24 24"><path d="M10 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h5M14 7l5 5-5 5m5-5H9"/></symbol>
    <symbol id="i-close" viewBox="0 0 24 24"><path d="M5 5 19 19M19 5 5 19"/></symbol>
  </svg>

  <header class="home-topbar">
    <div class="topbar-start">
      <span class="home-logo" aria-label="FacePuzzle">f</span>
      <span class="home-search"><svg class="icon"><use href="#i-search"/></svg><span>Search FacePuzzle</span></span>
    </div>
    <div class="topbar-nav" aria-hidden="true">
      <span class="is-active"><svg class="icon"><use href="#i-home"/></svg></span>
      <span><svg class="icon"><use href="#i-play"/></svg></span>
      <span><svg class="icon"><use href="#i-people"/></svg></span>
    </div>
    <div class="topbar-end"><span class="demo-pill">CLASS DEMO</span><span class="round-action" aria-hidden="true"><svg class="icon"><use href="#i-grid"/></svg></span><span class="round-action" aria-hidden="true"><svg class="icon"><use href="#i-bell"/></svg></span><span class="mini-avatar" aria-label="Your profile">H</span></div>
  </header>

  <div class="home-layout">
    <aside class="left-rail" id="quick-links" aria-label="Shortcuts">
      <div class="rail-link"><span class="rail-avatar">H</span><strong>Human (probably)</strong></div>
      <div class="rail-link"><span class="rail-icon rail-blue"><svg class="icon"><use href="#i-check"/></svg></span><strong>Verification</strong></div>
      <div class="rail-link"><span class="rail-icon rail-purple"><svg class="icon"><use href="#i-play"/></svg></span><strong>Stories</strong></div>
      <div class="rail-link"><span class="rail-icon rail-cyan"><svg class="icon"><use href="#i-people"/></svg></span><strong>Classmates</strong></div>
      <div class="rail-divider"></div>
      <p class="rail-label">Your shortcuts</p>
      <div class="rail-link"><span class="rail-icon rail-orange">?</span><strong>Robot Council</strong></div>
      <div class="rail-link"><span class="rail-icon rail-pink"><svg class="icon"><use href="#i-gift"/></svg></span><strong>Birthdays</strong></div>
      <p class="rail-footnote">FacePuzzle is a classroom demo. This feed is fictional.</p>
    </aside>

    <main id="home-feed" class="feed-column">
      <div class="verified-strip" role="status"><svg class="icon"><use href="#i-check"/></svg><span><strong>Verification complete.</strong> The feed is yours, human. Probably.</span></div>

      <section class="composer-card" aria-label="Create a post">
        <div class="composer-row"><span class="feed-avatar">H</span><span class="composer-prompt">What's on your mind, human?</span></div>
        <div class="composer-options"><span><svg class="icon"><use href="#i-photo"/></svg> Photos</span><span><svg class="icon"><use href="#i-people"/></svg> Classmates</span><span><svg class="icon"><use href="#i-check"/></svg> Verified mood</span></div>
      </section>

      <section id="stories" class="stories-section" aria-label="Stories">
        <div class="section-heading"><h1>Stories</h1><span>Today's plot twists</span></div>
        <div class="story-list">
          <div class="story-card create-story"><span class="story-create-art">+</span><span class="story-caption">Create story</span></div>
          <?php foreach ($stories as $index => $story): ?>
            <div class="story-card story-<?= htmlspecialchars($story['color'], ENT_QUOTES, 'UTF-8') ?>">
              <img src="<?= htmlspecialchars(guess_image_url($story['image']), ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy"><span class="story-ring">✓</span><span class="story-caption"><?= htmlspecialchars($story['title'], ENT_QUOTES, 'UTF-8') ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <div id="feed-posts" class="post-list">
        <article id="verification-post" class="feed-card">
          <header class="post-header"><span class="post-avatar post-avatar-blue">f</span><div><strong>FacePuzzle Verification</strong><span>Just now · <span aria-label="Public">◎</span></span></div><span class="post-menu" aria-hidden="true">···</span></header>
          <p class="post-copy">You solved three jigsaws, guessed five mystery pictures, and clicked “I’m not a robot” more than anyone should have to.</p>
          <p class="post-copy">Congratulations. You may now scroll like a human. 🎉</p>
          <div class="verified-art"><span class="art-seal"><svg class="icon"><use href="#i-check"/></svg></span><span>HUMAN<br><strong>VERIFIED</strong></span><small>Puzzle-solving skills: questionable.</small></div>
          <div class="post-counts"><span>👍 <span class="like-count">128</span> classmates approve</span><span>4 comments</span></div>
          <div class="post-actions" aria-hidden="true"><span><svg class="icon"><use href="#i-like"/></svg> Like</span><span><svg class="icon"><use href="#i-comment"/></svg> Comment</span><span><svg class="icon"><use href="#i-share"/></svg> Share</span></div>
          <div class="post-comment"><span class="comment-avatar">R</span><p><strong>Robot Council</strong> We are reviewing your suspiciously good puzzle skills.</p></div>
        </article>

        <article id="robot-post" class="feed-card">
          <header class="post-header"><span class="post-avatar post-avatar-violet">?</span><div><strong>Robot Council</strong><span>2 minutes ago · <span aria-label="Public">◎</span></span></div><span class="post-menu" aria-hidden="true">···</span></header>
          <p class="post-copy">We thought five mystery pictures would stop you. You knew the pig. You knew the people. You even remembered where the jigsaw pieces went.</p>
          <p class="post-copy">Our official verdict: <strong>annoyingly human.</strong></p>
          <div class="robot-quote">“Suspiciously robot-like behavior.”<span>— us, approximately six minutes ago</span></div>
          <div class="post-counts"><span>😮 <span class="like-count">42</span> reactions</span><span>2 comments</span></div>
          <div class="post-actions" aria-hidden="true"><span><svg class="icon"><use href="#i-like"/></svg> Like</span><span><svg class="icon"><use href="#i-comment"/></svg> Comment</span><span><svg class="icon"><use href="#i-share"/></svg> Share</span></div>
          <div class="post-comment"><span class="comment-avatar">H</span><p><strong>Human (probably)</strong> Please never ask me to solve another CAPTCHA.</p></div>
        </article>
      </div>
      <p class="feed-end">You've reached the end of your very hard-earned feed.</p>
    </main>

    <aside class="right-rail" aria-label="More from FacePuzzle">
      <section class="right-section"><h2>Sponsored</h2><div class="sponsor-card"><div class="sponsor-art"><span>NO MORE<br><strong>CAPTCHAS</strong></span></div><div><strong>Take a break. You earned it.</strong><span>facepuzzle.demo</span></div></div></section>
      <section id="birthday-card" class="right-section"><h2>Birthdays</h2><div class="birthday-row"><span class="birthday-icon"><svg class="icon"><use href="#i-gift"/></svg></span><p>It's the birthday of your <strong>last remaining brain cell.</strong> Wish it well.</p></div></section>
      <section id="classmates" class="right-section"><h2>Classmates</h2><div class="contact-row"><span class="contact-avatar contact-one">P</span><strong>Puzzle survivor</strong><span class="online-dot"></span></div><div class="contact-row"><span class="contact-avatar contact-two">G</span><strong>Guessing champion</strong><span class="online-dot"></span></div><div class="contact-row"><span class="contact-avatar contact-three">R</span><strong>Robot Council</strong><span class="online-dot"></span></div></section>
    </aside>
  </div>

</body>
</html>
