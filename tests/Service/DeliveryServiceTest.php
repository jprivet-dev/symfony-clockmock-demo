<?php

namespace App\Tests\Service;

use App\PolyFill\DateTimeImmutable;
use App\Service\DeliveryService;
use App\Tests\ClockMock;
use PHPUnit\Framework\TestCase;

class DeliveryServiceTest extends TestCase
{
    private DeliveryService $service;

    protected function setUp(): void
    {
        ClockMock::register(DeliveryService::class);
        ClockMock::register(DateTimeImmutable::class);
        $this->service = new DeliveryService();
    }

    public function testCanPlaceOrderBeforeCutoff(): void
    {
        // Simulate time: 2025-07-01 14:00:00 (2 PM)
        ClockMock::withClockMock(strtotime('2025-07-01 14:00:00'));
        $this->assertTrue($this->service->canPlaceOrderForTodayProcessing(), 'Order should be placeable before 5 PM cutoff.');
    }

    public function testCannotPlaceOrderAfterCutoff(): void
    {
        // Simulate time: 2025-07-01 17:30:00 (5:30 PM)
        ClockMock::withClockMock(strtotime('2025-07-01 17:30:00'));
        $this->assertFalse($this->service->canPlaceOrderForTodayProcessing(), 'Order should not be placeable after 5 PM cutoff.');
    }

    public function testEstimatedDeliveryDateBeforeCutoffMonday(): void
    {
        ClockMock::withClockMock(strtotime('2025-07-07 10:00:00'));
        $expectedDate = new DateTimeImmutable('2025-07-09 10:00:00');
        $this->assertEquals($expectedDate, $this->service->getEstimatedDeliveryDate());
    }

    public function testEstimatedDeliveryDateAfterCutoffMonday(): void
    {
        ClockMock::withClockMock(strtotime('2025-07-07 17:30:00'));
        $expectedDate = new DateTimeImmutable('2025-07-10 09:00:00');
        $this->assertEquals($expectedDate, $this->service->getEstimatedDeliveryDate());
    }

    public function testEstimatedDeliveryDateBeforeCutoffFriday(): void
    {
        ClockMock::withClockMock(strtotime('2025-07-11 10:00:00'));
        $expectedDate = new DateTimeImmutable('2025-07-15 10:00:00');
        $this->assertEquals($expectedDate, $this->service->getEstimatedDeliveryDate());
    }

    public function testEstimatedDeliveryDateAfterCutoffFriday(): void
    {
        ClockMock::withClockMock(strtotime('2025-07-11 17:30:00'));
        $expectedDate = new DateTimeImmutable('2025-07-16 09:00:00');
        $this->assertEquals($expectedDate, $this->service->getEstimatedDeliveryDate());
    }

    public function testIsDeliveryDateInPast(): void
    {
        ClockMock::withClockMock(strtotime('2025-07-10 10:00:00'));

        $pastDelivery = new DateTimeImmutable('2025-07-09 17:00:00');
        $this->assertTrue($this->service->isDeliveryDateInPast($pastDelivery), 'Past delivery date should be considered in the past.');

        $futureDelivery = new DateTimeImmutable('2025-07-11 09:00:00');
        $this->assertFalse($this->service->isDeliveryDateInPast($futureDelivery), 'Future delivery date should not be considered in the past.');
    }
}
