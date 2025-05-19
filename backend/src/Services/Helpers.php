<?php
declare(strict_types=1);

namespace App\Services;

class Helpers
{
    function generateVerificationCode(): string
    {
        $a = random_int(0, 9);
        $b = random_int(0, 9);
        $c = random_int(0, 9);

        return sprintf('%d%d%d%d%d%d', $a, $b, $c, $c, $b, $a);
    }

    function truncateText(string $text, int $length = 100): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . '...';
    }
}
