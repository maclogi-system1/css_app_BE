<?php

namespace App\Mixin;

use Closure;

/**
 * @mixin \Illuminate\Support\Str
 *
 * @method static Closure replaceArrayPreg
 */
class StrMixin
{
    public function replaceArrayPreg(): Closure
    {
        return function ($search, $replace, $value) {
            if (is_string($replace)) {
                $replace = [$replace];
            }

            preg_match_all($search, $value, $matches, PREG_PATTERN_ORDER);

            foreach (reset($matches) as $index => $match) {
                if (array_key_exists($index, $replace)) {
                    $value = str_replace($match, $replace[$index], $value);
                }
            }

            return $value;
        };
    }
}
