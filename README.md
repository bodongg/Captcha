# FacePuzzle

FacePuzzle is a funny, Facebook-inspired CAPTCHA project for a programming class. Log in with the demo account, solve three ten-piece jigsaw puzzles, guess five hidden pictures, and reach a static homepage as the ending.

This is a **classroom demo**. It is not Facebook, a real reCAPTCHA service, or secure authentication.

## Run locally

You need PHP 8.1 or newer. From this folder, start PHP's built-in web server:

```bash
php -S 127.0.0.1:8765
```

Then open [http://127.0.0.1:8765/login.php](http://127.0.0.1:8765/login.php).

Demo login:

```text
Email:    oneteamonegoal@gmail.com
Password: programming
```

The credentials are fixed in `lib/auth.php` for the class activity. Do not enter a real account password.

## How it works

1. Log in with the demo account.
2. Arrange the ten pieces of each jigsaw. On the third puzzle, check “I'm not a robot” and press **Verify**.
3. Guess each of the five pictures in order. A correct answer flips the image to reveal the face. Each picture also has a checkbox and **Verify** button.
4. After the fifth reveal, the static homepage appears as the ending. The feed controls are visual only.

Opening `login.php` always shows the login form. Logging in again starts the challenges from the beginning.

## Project structure

```text
funny-captcha/
├── login.php              Demo login and restart
├── index.php              Jigsaw and guessing challenge screens
├── verify.php             Server-side challenge checks
├── home.php               Static ending screen
├── lib/                   Login, puzzle, and answer rules
├── assets/
│   ├── css/               Page styles
│   ├── js/                Puzzle and picture-flip behavior
│   └── images/            Puzzle, guess, and reveal images
└── tests/                 Small PHP, JavaScript, and image checks
```

The two files in each guessing pair follow the pattern `name-guess.jpg` and `name.jpg`. The guess image appears first; the matching image is revealed only after a correct answer.

## Tests

Run these from the project folder:

```bash
php tests/auth_test.php
php tests/puzzle_test.php
php tests/guess_test.php
node tests/jigsaw-shape.test.js
```

The JavaScript test needs Node.js. On Windows, the image-pair check can also be run with `powershell -File tests/image_pair_test.ps1`.

## Before publishing

- Only publish photos that you have permission to share. Images committed to a public GitHub repository are public.
- The fixed login and CAPTCHA are for entertainment, not account security or bot protection.
- The app uses PHP sessions stored in the server's temporary directory. It works with a local PHP server, but it is **not configured for Vercel**. Vercel needs a PHP community runtime and a durable session store before this flow can be deployed reliably there.
