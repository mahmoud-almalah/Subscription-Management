<?php

declare(strict_types=1);

namespace App\Domain\Subscription\Enums;

enum SubscriptionStatusEnum: string
{
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';
    case PAUSED = 'paused';
    case EXPIRED = 'expired';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::CANCELLED => 'Cancelled',
            self::PAUSED => 'Paused',
            self::EXPIRED => 'Expired',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function isPaused(): bool
    {
        return $this === self::PAUSED;
    }

    public function isExpired(): bool
    {
        return $this === self::EXPIRED;
    }
}
