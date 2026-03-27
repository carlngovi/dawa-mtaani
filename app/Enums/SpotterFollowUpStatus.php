<?php

namespace App\Enums;

enum SpotterFollowUpStatus: string
{
    case Open = 'open';
    case Completed = 'completed';
    case Overdue = 'overdue';

    public function color(): string
    {
        return match ($this) {
            self::Open => 'yellow-400',
            self::Completed => 'green-400',
            self::Overdue => 'red-400',
        };
    }
}
