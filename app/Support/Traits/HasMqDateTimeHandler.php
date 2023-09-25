<?php

namespace App\Support\Traits;

use Carbon\CarbonPeriod;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

trait HasMqDateTimeHandler
{
    /**
     * Get a list of year-month in a range.
     */
    public function getDateTimeRange($fromDate, $toDate, array|string $options = 'Y-m'): array
    {
        if (is_string($options)) {
            $options = ['format' => 'Y-m'];
        }

        $step = Arr::get($options, 'step', '1 month');
        $format = Arr::get($options, 'format', 'Y-m');
        $period = new CarbonPeriod($fromDate, $step, $toDate);

        $result = [];

        foreach ($period as $dateTime) {
            $result[] = $dateTime->format($format);
        }

        return $result;
    }

    /**
     * Get date range for filter. Returned [ from_date, to_date ].
     */
    public function getDateRangeFilter(array $filters): array
    {
        $fromDate = Carbon::create(Arr::get($filters, 'from_date', now()->subYears(2)->month(1)->format('Y-m')));
        $fromDate->year($this->checkAndGetYearForFilter($fromDate->year));
        $toDate = Carbon::create(Arr::get($filters, 'to_date', now()->addYear()->month(12)->format('Y-m')));
        $toDate->year($this->checkAndGetYearForFilter($toDate->year));

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ];
    }

    /**
     * Check for filter by year, filter only year not less than current by 2 years
     * and year not more than present by 1 year.
     */
    public function checkAndGetYearForFilter($year): int
    {
        $year = $year < now()->subYear(2)->year ? now()->subYear(2)->year : $year;
        $year = $year > now()->addYear()->year ? now()->addYear()->year : $year;

        return $year;
    }
}
