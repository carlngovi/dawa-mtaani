<?php

namespace App\Enums;

enum SpotterSubmissionStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Held = 'held';
    case SrReviewed = 'sr_reviewed';
    case CcVerified = 'cc_verified';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::Held => 'Held for Review',
            self::SrReviewed => 'SR Reviewed',
            self::CcVerified => 'CC Verified',
            self::Accepted => 'Accepted',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray-400',
            self::Submitted => 'yellow-400',
            self::Held => 'orange-400',
            self::SrReviewed => 'blue-400',
            self::CcVerified => 'purple-400',
            self::Accepted => 'green-400',
            self::Rejected => 'red-400',
        };
    }

    public function badgeClass(): string
    {
        return 'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-' . $this->color() . '/20 text-' . $this->color();
    }
}
