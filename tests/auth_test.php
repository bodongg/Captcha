<?php
declare(strict_types=1);

require __DIR__ . '/../lib/auth.php';

function check(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

check(valid_demo_login('oneteamonegoal@gmail.com', 'programming'), 'valid login');
check(valid_demo_login('ONETEAMONEGOAL@GMAIL.COM', 'programming'), 'email case');
check(!valid_demo_login('oneteamonegoal@gmail.com', 'wrong'), 'reject wrong password');
check(!valid_demo_login('someone@example.com', 'programming'), 'reject wrong email');

echo "All login checks passed.\n";
