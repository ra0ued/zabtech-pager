<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class GpsLinkExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('gps_to_link', $this->gpsToLink(...), ['is_safe' => ['html']]),
        ];
    }

    public function gpsToLink(string $text, string $mapService = 'google'): string
    {
        // Regular expressions for coordinates (latitude, longitude)
        $patterns = [
            // 55.7558, 37.6173
            '/(-?\d{1,3}\.\d{1,7}),\s*(-?\d{1,3}\.\d{1,7})/',
            // 55°45'32.9"N, 37°37'02.3"E
            '/(\d{1,2})°(\d{1,2})\'(\d{1,2}\.\d{1,7})"([NS]),\s*(\d{1,3})°(\d{1,2})\'(\d{1,2}\.\d{1,7})"([EW])/i',
            // 55°45.548'N, 37°37.038'E
            '/(\d{1,2})°\s?(\d{1,2}\.\d{1,7})\'\s?([NS]),\s*(\d{1,3})°\s?(\d{1,2}\.\d{1,7})\'\s?([EW])/i',
        ];

        $result = $text;

        // Processing every format
        foreach ($patterns as $pattern) {
            $result = preg_replace_callback($pattern, function ($matches) use ($mapService) {
                // Extracting coordinates
                $coords = $this->parseCoordinates($matches);

                if (!$coords || !$this->isValidCoordinates($coords['lat'], $coords['lon'])) {
                    return $matches[0]; // Return source text if coordinates are invalid
                }

                $latitude = $coords['lat'];
                $longitude = $coords['lon'];

                // Generating a link
                $link = $this->generateMapLink($latitude, $longitude, $mapService);

                return '<a href="' . $link . '" target="_blank">' . $matches[0] . '</a>';
            }, $result);
        }

        return $result;
    }

    private function parseCoordinates(array $matches): ?array
    {
        // Decimal degrees
        if (count($matches) === 3) {
            return [
                'lat' => (float)$matches[1],
                'lon' => (float)$matches[2],
            ];
        }

        // Degrees, minutes, seconds
        if (count($matches) === 9) {
            $latDegrees = (int)$matches[1];
            $latMinutes = (int)$matches[2];
            $latSeconds = (float)$matches[3];
            $latDirection = strtoupper($matches[4]);
            $lonDegrees = (int)$matches[5];
            $lonMinutes = (int)$matches[6];
            $lonSeconds = (float)$matches[7];
            $lonDirection = strtoupper($matches[8]);

            $latitude = $latDegrees + ($latMinutes / 60) + ($latSeconds / 3600);
            if ($latDirection === 'S') {
                $latitude = -$latitude;
            }

            $longitude = $lonDegrees + ($lonMinutes / 60) + ($lonSeconds / 3600);
            if ($lonDirection === 'W') {
                $longitude = -$longitude;
            }

            return [
                'lat' => $latitude,
                'lon' => $longitude,
            ];
        }

        // Degrees, minutes
        if (count($matches) === 7) {
            $latDegrees = (int)$matches[1];
            $latMinutes = (float)$matches[2];
            $latDirection = strtoupper($matches[3]);
            $lonDegrees = (int)$matches[4];
            $lonMinutes = (float)$matches[5];
            $lonDirection = strtoupper($matches[6]);

            $latitude = $latDegrees + ($latMinutes / 60);
            if ($latDirection === 'S') {
                $latitude = -$latitude;
            }

            $longitude = $lonDegrees + ($lonMinutes / 60);
            if ($lonDirection === 'W') {
                $longitude = -$longitude;
            }

            return [
                'lat' => $latitude,
                'lon' => $longitude,
            ];
        }

        return null;
    }

    private function isValidCoordinates(float $latitude, float $longitude): bool
    {
        return $latitude >= -90 && $latitude <= 90 && $longitude >= -180 && $longitude <= 180;
    }

    private function generateMapLink(string $latitude, string $longitude, string $mapService): string
    {
        return match (strtolower($mapService)) {
            '2gis' => "https://2gis.com/geo/$longitude,$latitude?z=15",
            'nakarte' => "https://nakarte.me/#m=15/$latitude/$longitude&l=O/Wp&nktp=$latitude/$longitude/",
            'openstreetmap' => "https://www.openstreetmap.org/?mlat=$latitude&mlon=$longitude&zoom=15",
            'yandex' => "https://yandex.ru/maps/?ll=$longitude,$latitude&z=15",
            default => "https://www.google.com/maps?q=$latitude,$longitude&z=15",
        };
    }
}