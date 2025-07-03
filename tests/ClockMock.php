<?php

namespace App\Tests;

/**
 * We're using Symfony 7.3 (actual stable release), but ClockMock::strtotime() won't be available until version 7.4.
 * Using an overload in the meantime...
 *
 * @see "[PhpUnitBridge] Add strtotime() to ClockMock": https://github.com/symfony/symfony/pull/60424
 */
class ClockMock extends \Symfony\Bridge\PhpUnit\ClockMock
{
    /**
     * @return false|int
     */
    public static function strtotime(string $datetime, ?int $timestamp = null)
    {
        if (null === $timestamp) {
            $timestamp = self::time();
        }

        return \strtotime($datetime, $timestamp);
    }

    public static function register($class): void
    {
        $self = static::class;

        $mockedNs = [substr($class, 0, strrpos($class, '\\'))];
        if (0 < strpos($class, '\\Tests\\')) {
            $ns = str_replace('\\Tests\\', '\\', $class);
            $mockedNs[] = substr($ns, 0, strrpos($ns, '\\'));
        } elseif (0 === strpos($class, 'Tests\\')) {
            $mockedNs[] = substr($class, 6, strrpos($class, '\\') - 6);
        }
        foreach ($mockedNs as $ns) {
            if (\function_exists($ns.'\time')) {
                continue;
            }
            eval(<<<EOPHP
namespace $ns;

function time()
{
    return \\$self::time();
}

function microtime(\$asFloat = false)
{
    return \\$self::microtime(\$asFloat);
}

function sleep(\$s)
{
    return \\$self::sleep(\$s);
}

function usleep(\$us)
{
    \\$self::usleep(\$us);
}

function date(\$format, \$timestamp = null)
{
    return \\$self::date(\$format, \$timestamp);
}

function gmdate(\$format, \$timestamp = null)
{
    return \\$self::gmdate(\$format, \$timestamp);
}

function hrtime(\$asNumber = false)
{
    return \\$self::hrtime(\$asNumber);
}

function strtotime(\$datetime, \$timestamp = null)
{
    return \\$self::strtotime(\$datetime, \$timestamp);
}
EOPHP
            );
        }
    }
}
