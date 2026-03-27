<?php

namespace App\Services;

use App\Models\SpotterSubmission;
use App\Models\WardAdjacency;

class SpotterDuplicateDetector
{
    public function normalise(string $name): string
    {
        $name = strtolower(trim($name));
        $name = preg_replace('/[^a-z0-9\s]/', '', $name);

        $suffixes = ['pharmacies', 'pharmacy', 'chemists', 'chemist', 'pharmas', 'pharma', 'pharms', 'pharm'];
        foreach ($suffixes as $s) {
            $name = preg_replace('/\b' . preg_quote($s, '/') . '\b/', 'pharm', $name);
        }

        $name = preg_replace('/\s+/', ' ', trim($name));

        return $name;
    }

    public function levenshteinDistance(string $a, string $b): int
    {
        return levenshtein($this->normalise($a), $this->normalise($b));
    }

    public function haversineMetres(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R = 6371000;
        $phi1 = deg2rad($lat1);
        $phi2 = deg2rad($lat2);
        $dPhi = deg2rad($lat2 - $lat1);
        $dLambda = deg2rad($lng2 - $lng1);

        $a = sin($dPhi / 2) ** 2 + cos($phi1) * cos($phi2) * sin($dLambda / 2) ** 2;

        return round($R * 2 * atan2(sqrt($a), sqrt(1 - $a)), 2);
    }

    public function checkExact(string $pharmacyName, string $county): ?SpotterSubmission
    {
        $norm = $this->normalise($pharmacyName);

        return SpotterSubmission::whereNotIn('status', ['draft', 'rejected'])
            ->where('county', $county)
            ->get()
            ->first(fn ($s) => $this->normalise($s->pharmacy) === $norm);
    }

    public function checkFuzzy(string $pharmacyName, string $wardId): ?SpotterSubmission
    {
        $wards = WardAdjacency::getAdjacentWards($wardId);

        return SpotterSubmission::whereNotIn('status', ['draft', 'rejected'])
            ->whereIn('ward', $wards)
            ->get()
            ->first(fn ($s) => $this->levenshteinDistance($pharmacyName, $s->pharmacy) <= 2);
    }

    public function checkGps(float $lat, float $lng, string $county, float $threshold = 50): ?SpotterSubmission
    {
        return SpotterSubmission::whereNotIn('status', ['draft', 'rejected'])
            ->where('county', $county)
            ->get()
            ->sortBy(fn ($s) => $this->haversineMetres($lat, $lng, (float) $s->lat, (float) $s->lng))
            ->first(fn ($s) => $this->haversineMetres($lat, $lng, (float) $s->lat, (float) $s->lng) <= $threshold);
    }

    public function run(string $pharmacyName, string $wardId, string $county, float $lat, float $lng): array
    {
        $exact = $this->checkExact($pharmacyName, $county);
        if ($exact) {
            return ['exact' => $exact, 'fuzzy' => null, 'gps' => null];
        }

        return [
            'exact' => null,
            'fuzzy' => $this->checkFuzzy($pharmacyName, $wardId),
            'gps' => $this->checkGps($lat, $lng, $county),
        ];
    }
}
