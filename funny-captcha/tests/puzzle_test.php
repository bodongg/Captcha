<?php
declare(strict_types=1);

require __DIR__ . '/../lib/puzzle.php';

function expect_puzzle(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

expect_puzzle(valid_puzzle_placements(range(0, 9)), 'accept complete puzzle');
expect_puzzle(!valid_puzzle_placements(range(0, 8)), 'reject incomplete puzzle');
expect_puzzle(!valid_puzzle_placements([0, 1, 2, 3, 4, 5, 6, 7, 9, 8]), 'reject swapped pieces');
expect_puzzle(!valid_puzzle_placements([0, 1, 2, 3, 4, 5, 6, 7, 8, '9']), 'reject string value');

echo "All puzzle checks passed.\n";
