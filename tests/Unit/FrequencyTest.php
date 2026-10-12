<?php

namespace Tests\Unit;

use App\Frequency;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class FrequencyTest extends TestCase
{
    public function test_due_dates_per_frequency(): void
    {
        $start = CarbonImmutable::parse('2026-01-31');

        $this->assertSame('2026-01-31', Frequency::Weekly->dueDate($start, 1)->toDateString());
        $this->assertSame('2026-02-07', Frequency::Weekly->dueDate($start, 2)->toDateString());
        $this->assertSame('2026-02-14', Frequency::Biweekly->dueDate($start, 2)->toDateString());
        $this->assertSame('2026-02-28', Frequency::Monthly->dueDate($start, 2)->toDateString());
        $this->assertSame('2026-04-30', Frequency::Monthly->dueDate($start, 4)->toDateString());
    }
}
