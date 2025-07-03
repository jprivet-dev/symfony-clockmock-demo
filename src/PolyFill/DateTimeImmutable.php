<?php

namespace App\PolyFill;

use DateTimeZone;

class DateTimeImmutable extends \DateTimeImmutable
{
    /**
     * ISO 8601 simplified format (Year-Month-Day Hour:Minute:Second).
     * Commonly used in databases (e.g., MySQL DATETIME) and data exchange.
     * Example: 2025-07-03 14:30:00
     */
    public const ISO8601_DATE_TIME = 'Y-m-d H:i:s';

    public function __construct(string $datetime = 'now', ?DateTimeZone $timezone = null)
    {
        parent::__construct('now' === $datetime ? '@'.time() : $datetime, $timezone);
    }

    public function getHour(): int
    {
        return $this->format('H');
    }

    public function getWeekNumber(): string
    {
        return $this->format('W');
    }

    public function getDayNumberOnYear(): string
    {
        return $this->format('z');
    }

    public function getDayNumber(): int
    {
        return $this->format('N');
    }

    public function isWeekend(): bool
    {
        return in_array($this->getDayNumber(), [6, 7]);
    }

    public function modify(string $modifier): static
    {
        $dateTime = parent::modify($modifier);

        return new static($dateTime->getTimestamp(), $dateTime->getTimezone());
    }
}
