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
        $pattern = '/Message from HF Pager Gate \(from (\d+) via (\d+)\)/i';

        $result = preg_replace_callback($pattern, function ($matches) {
            $id1 = $matches[1];
            $id2 = $matches[2];
            $link = sprintf('<a href="/%s" target="_blank">%s</a>', $id1, $id1);

            return sprintf('%s через %s', $link, $id2);
        }, $header);

        return $result ?? $header;
    }
}