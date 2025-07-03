<?php

namespace App\Service;


use App\PolyFill\DateTimeImmutable;
use DateMalformedStringException;

/**
 * The following class is not optimal. It deliberately contains couplings with DateTimeImmutable and strtotime,
 * in order to see how to mock time in uncomfortable situations.
 */
class DeliveryService
{
    private const int ORDER_CUTOFF_HOUR = 17; // 17h00 (5 PM)
    private const string DELIVERY_IN_2_WORKING_DAYS = '+2 weekdays'; // Delivery in 2 working days

    /**
     * Checks if an order can be placed for same-day processing.
     */
    public function canPlaceOrderForTodayProcessing(): bool
    {
        return new DateTimeImmutable()->getHour() < self::ORDER_CUTOFF_HOUR;
    }

    /**
     * Calculates the estimated delivery date.
     * @throws DateMalformedStringException
     */
    public function getEstimatedDeliveryDate(): DateTimeImmutable
    {
        $currentDateTime = new DateTimeImmutable();

        if(!$this->canPlaceOrderForTodayProcessing()) {
            $currentDateTime = $currentDateTime->modify('tomorrow 09:00:00');
            if ($currentDateTime->isWeekend()) {
                $currentDateTime = $currentDateTime->modify('monday 09:00:00');
            }
        }

        $deliveryTimestamp = strtotime(self::DELIVERY_IN_2_WORKING_DAYS, $currentDateTime->getTimestamp());

        return new DateTimeImmutable()->setTimestamp($deliveryTimestamp);
    }

    /**
     * Checks if a specific delivery date is in the past compared to current time.
     */
    public function isDeliveryDateInPast(DateTimeImmutable $deliveryDate): bool
    {
        return (new DateTimeImmutable()) > $deliveryDate;
    }
}
