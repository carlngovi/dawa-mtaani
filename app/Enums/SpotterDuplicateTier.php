<?php

namespace App\Enums;

enum SpotterDuplicateTier: string
{
    case Sr = 'sr';
    case Cc = 'cc';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Sr => 'Sales Rep',
            self::Cc => 'County Coordinator',
            self::Admin => 'Admin',
        };
    }
}
