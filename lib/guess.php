<?php
declare(strict_types=1);

function guess_rounds(): array
{
    return [
        ['guess' => 'pig-guess.jpg', 'reveal' => 'pig.jpg', 'answer' => 'Pig'],
        ['guess' => 'rene-guess.jpg', 'reveal' => 'rene.jpg', 'answer' => 'Charlie Kirk'],
        ['guess' => 'dacera-guess.jpg', 'reveal' => 'dacera.jpg', 'answer' => 'Lyle Zambo'],
        ['guess' => 'james-guess.jpg', 'reveal' => 'james.jpg', 'answer' => 'Russel Tulod'],
        ['guess' => 'malik-guess.jpg', 'reveal' => 'malik.jpg', 'answer' => 'Denmark Sinday'],
    ];
}

function guess_is_correct(int $round, string $answer): bool
{
    $rounds = guess_rounds();
    if (!isset($rounds[$round])) {
        return false;
    }

    $normal = static fn (string $value): string => strtolower(trim((string) preg_replace('/\s+/', ' ', $value)));
    return $normal($answer) === $normal($rounds[$round]['answer']);
}

function guess_image_url(string $filename): string
{
    $hash = hash_file('sha256', __DIR__ . '/../assets/images/' . $filename);
    if ($hash === false) {
        throw new RuntimeException('Guess challenge image is missing: ' . $filename);
    }
    return 'assets/images/' . rawurlencode($filename) . '?v=' . substr($hash, 0, 8);
}
