<?php
declare(strict_types=1);

function valid_demo_login(string $email, string $password): bool
{
    return strtolower(trim($email)) === 'oneteamonegoal@gmail.com'
        && hash_equals('programming', $password);
}
