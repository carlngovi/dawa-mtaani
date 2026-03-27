<?php

namespace App\Enums;

enum SpotterDuplicateDecision: string
{
    case ConfirmedDuplicate = 'confirmed_duplicate';
    case NotDuplicate = 'not_duplicate';
    case Pending = 'pending';
}
