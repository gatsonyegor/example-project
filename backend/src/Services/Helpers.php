<?php
declare(strict_types=1);

namespace App\Services;

class Helpers
{
    function truncateText(string $text, int $length = 100): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return mb_substr($text, 0, $length) . '...';
    }
}
