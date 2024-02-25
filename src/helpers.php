<?php

use FmTod\Money\Money;
use Illuminate\Support\Carbon;

if (! function_exists('human_file_size')) {
    /**
     * Returns a human-readable file size.
     *
     * @param  int  $bytes
     *                      Byte contains the size of the bytes to convert
     * @param  int  $decimals
     *                         Number of decimal places to be returned
     * @return string a string in human-readable format
     *
     * */
    function human_file_size(int $bytes, int $decimals = 2): string
    {
        $sz = 'BKMGTPE';
        $factor = (int) floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / (1024 ** $factor)).$sz[$factor];
    }
}

if (! function_exists('in_arrayi')) {
    /**
     * Checks if a value exists in an array in a case-insensitive manner.
     *
     * @param  mixed  $needle
     *                         The searched value
     * @param  $haystack
     *                   The array
     * @param  bool  $strict  [optional]
     *                        If set to true type of needle will also be matched
     * @return bool true if needle is found in the array,
     *              false otherwise
     */
    function in_arrayi($needle, $haystack, bool $strict = false): bool
    {
        return in_array(strtolower($needle), array_map('strtolower', $haystack), $strict);
    }
}

if (! function_exists('uniord')) {
    /**
     * Checks if a value exists in an array in a case-insensitive manner.
     *
     * @param  $string
     *                 Character to be converted to unicode
     * @param  string  $encoding
     *                            Encoding to use
     * @return string Encoded character
     */
    function uniord($string, string $encoding = 'UTF-8'): string
    {
        $entity = mb_encode_numericentity($string, [0x0, 0xFFFF, 0, 0xFFFF], $encoding);

        return preg_replace('`^&#(\d+);.*$`', '\\1', $entity);
    }
}

if (! function_exists('clean_css_id')) {
    /**
     * Checks if a value exists in an array in a case-insensitive manner.
     *
     * @param  $string
     *                 string to be cleaned
     * @return string Cleaned CSS ID
     */
    function clean_css_id($string): string
    {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", '', $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", ' ', $string);

        //Convert whitespaces and underscore to dash
        return preg_replace("/[\s_]/", '-', $string);
    }
}

if (! function_exists('array_contains')) {
    /**
     * Checks if a value exists in an array in a case-insensitive manner.
     *
     * @param  string  $needle  String to find in array
     * @param  bool  $starts_with  Specify if the string can be found anywhere on the array key or just on the start
     * @return bool Return true if any of the array keys contains the specified string, otherwise return false
     */
    function array_contains(string $needle, array $haystack, bool $starts_with = false): bool
    {
        foreach ($haystack as $search) {
            if ((strpos($search, $needle) !== false && $starts_with === false) || (strpos($search, $needle) === 0 && $starts_with === true)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('sanitize_input')) {
    /**
     * Escape the input.
     */
    function sanitize_input($value): string
    {
        return htmlspecialchars(strip_tags($value), ENT_NOQUOTES);
    }
}

if (! function_exists('parse_expires_in')) {
    /**
     * Parse expires_in times.
     */
    function parse_expires_in(int $value, bool $safe = false): Carbon
    {
        $expDate = Carbon::now()->addSeconds($value);
        if ($safe) {
            $expDate = $expDate->subSeconds($safe === true ? 120 : $safe);
        }

        return $expDate;
    }
}

if (! function_exists('print_local_datetime')) {
    /**
     * Print local time using momentjs.
     */
    function print_local_datetime($date, string $format = 'yyyy-MM-DD h:mm A'): string
    {
        return "<script>document.write(moment.utc('$date').local().format('$format'))</script>";
    }
}

if (! function_exists('when')) {
    /**
     * Apply the callback if the condition is truthy.
     */
    function when($condition, mixed $value, ?callable $callback): mixed
    {
        if (is_callable($condition) && $condition($value)) {
            return $callback($value);
        }

        return $condition ? $callback($value) : $value;
    }
}

if (! function_exists('method_defined')) {
    /**
     * Checks if a class defines a certain method.
     */
    function method_defined($ref, $method): bool
    {
        $class = (is_string($ref)) ? $ref : get_class($ref);

        return method_exists($class, $method) && ($class === (new \ReflectionMethod($class, $method))->getDeclaringClass()->name);
    }
}

if (! function_exists('money_add')) {
    /**
     * Parse arguments into money objects and add them together.
     */
    function money_add(...$addends): Money
    {
        $currency = config('money.currency');

        return array_reduce($addends, static function (?Money $total, $money) use ($currency) {
            if (! $money instanceof Money) {
                $money = Money::parse($money, $total?->getCurrency() ?? $currency);
            }

            return is_null($total) ? $money : $total->add($money);
        }, null);
    }
}

if (! function_exists('money_subtract')) {
    /**
     * Parse arguments into money objects and subtract them.
     */
    function money_subtract(...$subtrahends): Money
    {
        $currency = config('money.currency');

        return array_reduce($subtrahends, static function (?Money $total, $money) use ($currency) {
            if (! $money instanceof Money) {
                $money = Money::parse($money, $total?->getCurrency() ?? $currency);
            }

            return is_null($total) ? $money : $total->subtract($money);
        }, null);
    }
}
