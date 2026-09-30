<?php
declare(strict_types=1);

$source = __DIR__ . '/../lib/guess.php';
if (!is_file($source)) {
    fwrite(STDERR, "Guess challenge behavior is not implemented yet.\n");
    exit(1);
}
require $source;

function check(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$rounds = guess_rounds();
check(count($rounds) === 5, 'There must be five picture rounds.');
check(array_column($rounds, 'guess') === ['pig-guess.jpg', 'rene-guess.jpg', 'dacera-guess.jpg', 'james-guess.jpg', 'malik-guess.jpg'], 'Guess pictures must stay in the requested order.');
check(array_column($rounds, 'reveal') === ['pig.jpg', 'rene.jpg', 'dacera.jpg', 'james.jpg', 'malik.jpg'], 'Reveal pictures must match their guess pictures.');
check(array_column($rounds, 'answer') === ['Pig', 'Charlie Kirk', 'Lyle Zambo', 'Russel Tulod', 'Denmark Sinday'], 'Answers must stay in the requested order.');
check(guess_is_correct(0, '  pIg  '), 'Answers should ignore case and surrounding whitespace.');
check(guess_is_correct(1, 'Charlie   Kirk'), 'Extra spaces between names should be accepted.');
check(!guess_is_correct(1, 'Pig'), 'An answer from another round must be rejected.');
check(!guess_is_correct(3, 'Russell Tulod'), 'A different spelling must be rejected.');
check(guess_is_correct(4, '  DENMARK   SINDAY '), 'The final answer should ignore case and extra whitespace.');
check(!guess_is_correct(4, 'Denmark'), 'An incomplete final answer must be rejected.');
check(!guess_is_correct(5, 'Pig'), 'A round outside the challenge must be rejected.');
check(function_exists('guess_image_url'), 'Cache-aware image URLs must be implemented.');
check((bool) preg_match('~^assets/images/dacera-guess\.jpg\?v=[a-f0-9]{8}$~', guess_image_url('dacera-guess.jpg')), 'Guess image URLs must change when their contents change.');
check((bool) preg_match('~^assets/images/james\.jpg\?v=[a-f0-9]{8}$~', guess_image_url('james.jpg')), 'Reveal image URLs must change when their contents change.');
check((bool) preg_match('~^assets/images/malik-guess\.jpg\?v=[a-f0-9]{8}$~', guess_image_url('malik-guess.jpg')), 'The final guess image must be available.');
check((bool) preg_match('~^assets/images/malik\.jpg\?v=[a-f0-9]{8}$~', guess_image_url('malik.jpg')), 'The final reveal image must be available.');

echo "Guess challenge tests passed.\n";
