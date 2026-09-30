<?php
declare(strict_types=1);

function valid_puzzle_placements(mixed $placements): bool
{
    if (!is_array($placements) || count($placements) !== 10 || array_keys($placements) !== range(0, 9)) {
        return false;
    }

    foreach ($placements as $position => $piece) {
        if (!is_int($piece) || $piece !== $position) {
            return false;
        }
    }

    return true;
}
