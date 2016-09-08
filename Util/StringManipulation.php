<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Util;

/**
 * Class StringManipulation.
 *
 * @category Util
 */
class StringManipulation
{
    /**
     * Like the PHP internal function vsprintf, but accepts $args keys instead of order index.
     * Based on function created by Josef Kufner <jkufner@gmail.com>.
     * First used for Aareon France usages.
     *
     * @param string $input The format input string.
     * @param string|array|object $args The arguments to replace from the format string.
     * @return string The formatted string to output.
     */
    public static function vksprintf(string $input, $args): string
    {
        !is_object($args) ?: $args = get_object_vars($args);
        is_array($args) ?: $args = [$args];

        $map = array_flip(array_keys($args));
        $callback = function ($m) use ($map, $input) {
            //If the variable to replace does not exist, remove the variable and replace with empty.
            if (!isset($map[$m[2]])) {
                return '';
            }
            return $m[1] . '%' . ($map[$m[2]] + 1) . '$' . $m[3];
        };

        $regExp = '#(^|[^%])%([a-zA-Z0-9_-]+)\$([-+ 0]?(\'.)?[0-9.]*[bcdeEfFgGosuxX])#';
        $output = preg_replace_callback($regExp, $callback, $input);

        return vsprintf($output, $args);
    }
}
