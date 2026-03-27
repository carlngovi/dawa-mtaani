<?php

namespace App\Enums;

enum SpotterPotential: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    public function color(): string
    {
        return match ($this) {
            self::High => 'green-400',
            self::Medium => 'yellow-400',
            self::Low => 'red-400',
        };
    }

    public function mapPin(): string
    {
        return match ($this) {
            self::High => '#22c55e',
            self::Medium => '#facc15',
            self::Low => '#ef4444',
        };
    }
}
