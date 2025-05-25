<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HeaderExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('header', [$this, 'parseHeader'], ['is_safe' => ['html']]),
        ];
    }

    public function parseHeader(string $header): string
    {
        $pattern = '/Message from HF Pager Gate \(from (\d+) via (\d+)(?:\/(\d+))?\)/i';

        $result = preg_replace_callback($pattern, function ($matches) {
            $id1 = $matches[1];
            $id2 = $matches[2];
            $id3 = $matches[3] ?? '';
            $link = sprintf('<a href="/%s" target="_blank">%s</a>', $id1, $id1);

            $viaPart = $id3 ? "$id2/$id3" : $id2;

            return sprintf('%s через %s', $link, $viaPart);
        }, $header);

        return $result ?? $header;
    }
}