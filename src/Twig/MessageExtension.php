<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MessageExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('message_format', $this->hfMessageFormat(...), ['is_safe' => ['html']]),
        ];
    }

    public function hfMessageFormat(string $text): string
    {
        // Regexp for "Message from 1204(#229) to 3333, 5.9 Bd, ER=0.0% : Message text"
        $pattern = '/Message from (\d+)\(#(\d+)\) to (\d+),\s*(\d+\.\d+)\s*Bd,\s*ER=(\d+\.\d+%)\s*:\s*(.+)/i';

        return preg_replace_callback($pattern, function ($matches) {
            $id1 = $matches[1]; // Sender ID (1204)
            $num = $matches[2]; // (#229)
            $id2 = $matches[3]; // Repeater ID (3333)
            $speed = $matches[4]; // Speed (5.9)
            $error = $matches[5]; // Error (0.0%)
            $message = $matches[6]; // Message text

            return sprintf('%s(#%s), %s Bd, ER=%s: %s', $id1, $num, $speed, $error, $message);
        }, $text);
    }
}