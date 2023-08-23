<?php

namespace App\Constants;

class DateTimeConstant
{
    public const DAYS_OF_WEEK = [
        '日', '月', '火', '水', '木', '金', '土',
    ];

    public const TIMELINE = [
        'yesterday' => '昨日',
        'today' => '今日',
        'tomorrow' => '明日',
        'last_week' => '先週',
        'this_week' => '今週',
        'next_week' => '来週',
        'last_month' => '先月',
        'this_month' => '今月',
        'next_month' => '来月',
        'last_year' => '去年',
        'this_year' => '今年',
        'next_year' => '来年',
    ];

    public const TIME_UNITS = [
        'day' => '日',
        'week' => '週',
        'month' => '月',
        'year' => '年',
    ];
}
