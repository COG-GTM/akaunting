<?php

namespace Tests\Unit\Widgets;

use App\Utilities\Date;
use App\Widgets\ProfitLoss;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PeriodKeyTest extends TestCase
{
    protected function tearDown(): void
    {
        Date::setLocale('en');

        parent::tearDown();
    }

    public function testItMatchesParsingTheDateAgainInEveryLocale(): void
    {
        $widget = (new ReflectionClass(ProfitLoss::class))->newInstanceWithoutConstructor();
        $period_key = fn (...$args) => $this->getPeriodKey(...$args);
        $period_key = $period_key->bindTo($widget, $widget);

        foreach (['en-GB', 'ar-SA', 'fa-IR', 'de-DE', 'tr-TR', 'zh-CN'] as $locale) {
            Date::setLocale($locale);

            foreach (['2026-01-05 10:00:00', '2025-12-31 23:59:59', '2024-02-29 00:00:00'] as $value) {
                $date = Date::rawParse($value, 'Europe/Istanbul');

                $this->assertSame(Date::parse($date)->format('Y-m'), $period_key($date, 'Y-m', 'month'));
                $this->assertSame(Date::parse($date)->quarter, $period_key($date, 'Y-m', 'quarter'));
                $this->assertSame(Date::parse($value)->format('Y-m'), $period_key($value, 'Y-m', 'month'));
                $this->assertSame(Date::parse($value)->quarter, $period_key($value, 'Y-m', 'quarter'));
            }
        }
    }
}
