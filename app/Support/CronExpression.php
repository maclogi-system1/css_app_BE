<?php

namespace App\Support;

use Cron\CronExpression as BaseCronExpression;
use Illuminate\Support\Arr;

class CronExpression
{
    /**
     * @var \Cron\CronExpression
     */
    protected $cronExpression;

    public function __construct(array $schedule)
    {
        $minute = Arr::get($schedule, 'minute', '*') ?? '*';
        $hour = Arr::get($schedule, 'hour', '*') ?? '*';
        $day = Arr::get($schedule, 'day', '*') ?? '*';
        $month = Arr::get($schedule, 'month', '*') ?? '*';
        $dayOfWeek = Arr::get($schedule, 'day_of_week', '*') ?? '*';
        $weekOfMonth = Arr::get($schedule, 'weekOfMonth');

        if (! is_null($weekOfMonth)) {
            $dayOfWeek = "#{$weekOfMonth}";
        }

        $this->cronExpression = new BaseCronExpression("{$minute} {$hour} {$day} {$month} {$dayOfWeek}");
    }

    /**
     * Initialize a new cron expression.
     */
    public static function make(array $schedule): static
    {
        return new static($schedule);
    }

    public function __call(string $method, array $parameters): mixed
    {
        return $this->cronExpression->{$method}(...$parameters);
    }
}
