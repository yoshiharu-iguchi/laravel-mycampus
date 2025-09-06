<?php

namespace App\Enums;

enum EnrollmentStatus: int
{
    case Draft = 0;
    case Registered = 1;
    case Approved = 2;
    case Pending = 3;
    case Canceled = 4;

    public function transKey(): string
    {
        // enums.enrollment_status.registered など
        return 'enums.enrollment_status.' . strtolower($this->name);
    }

    public function label(): string
    {
        return __($this->transKey());
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Approved   => 'text-bg-success',
            self::Canceled   => 'text-bg-danger',
            self::Pending    => 'text-bg-warning',
            self::Registered => 'text-bg-warning',
            self::Draft      => 'text-bg-secondary',
        };
    }
}