<?php

declare(strict_types=1);

namespace RocIT\Tests\Unit;

use Generator;
use PHPUnit\Framework\TestCase;
use function array_filter;
use function is_array;
use function is_int;
use function is_string;
use function iterator_to_array;
use function RocIT\filter;
use function str_contains;
use const ARRAY_FILTER_USE_BOTH;
use const ARRAY_FILTER_USE_KEY;

final class IterableFilterTest extends TestCase
{
    use IterableMocker;

    public function dataWithoutCallback(): Generator
    {
        yield 'Simple array with default mode' => [
            fn() => ['hello', null, [], false, '', 0, 0.0, 'world'],
            [0 => 'hello', 7 => 'world'],
            null,
        ];

        yield 'Simple iterator with default mode' => [
            fn() => self::getGenerator(['hello', null, [], false, '', 0, 0.0, 'world']),
            [0 => 'hello', 7 => 'world'],
            null,
        ];

        yield 'Simple array with ARRAY_FILTER_USE_KEY mode' => [
            fn() => ['hello', 'world'],
            [1 => 'world'], // should be [0 => 'hello', 1 => 'world']
            ARRAY_FILTER_USE_KEY,
        ];

        yield 'Simple iterator with ARRAY_FILTER_USE_KEY mode' => [
            fn() => self::getGenerator(['hello', 'world']),
            [1 => 'world'], // should be [0 => 'hello', 1 => 'world']
            ARRAY_FILTER_USE_KEY,
        ];

        yield 'Simple array with ARRAY_FILTER_USE_BOTH mode' => [
            fn() => ['hello', null, [], false, '', 0, 0.0, 'world'],
            [0 => 'hello', 7 => 'world'],
            ARRAY_FILTER_USE_BOTH,
        ];

        yield 'Simple iterator with ARRAY_FILTER_USE_BOTH mode' => [
            fn() => ['hello', null, [], false, '', 0, 0.0, 'world'],
            [0 => 'hello', 7 => 'world'],
            ARRAY_FILTER_USE_BOTH,
        ];
    }

    /**
     * @covers       filter
     *
     * @dataProvider dataWithoutCallback
     */
    public function testSuccessfulWithoutCallback(callable $iteratorFactory, array $expectedResult, ?int $mode): void
    {
        $iterator       = $iteratorFactory();
        $resultIterator = filter(
            $iterator,
            mode: $mode ?: 0
        );

        self::assertIsIterable($resultIterator);
        self::assertIsNotArray($resultIterator);

        $resultArray = iterator_to_array($resultIterator);

        self::assertEquals($expectedResult, $resultArray);
    }

    /**
     * @covers       filter
     *
     * @dataProvider dataWithoutCallback
     */
    public function testSameAsNativeWithoutCallback(callable $iteratorFactory, array $expectedResult, ?int $mode): void
    {
        $iterator       = $iteratorFactory();
        $resultIterator = filter(
            $iterator,
            mode: $mode ?: 0
        );

        $resultArray = iterator_to_array($resultIterator);

        $iterator = $iteratorFactory();
        self::assertEquals(
            array_filter(
                is_array($iterator) === true ? $iterator : iterator_to_array($iterator),
                static function ($value) {
                    return !empty($value);
                },
                mode: $mode ?: 0
            ),
            $resultArray
        );
    }

    public function dataWithCallback(): Generator
    {
        $callback = static function ($value): bool {
            return is_string($value) === true && str_contains($value, 'o');
        };

        yield 'Simple array with default mode' => [
            fn() => ['hello', null, [], false, '', 0, 0.0, 'world'],
            $callback,
            [0 => 'hello', 7 => 'world'],
            null,
        ];

        yield 'Simple iterator with default mode' => [
            fn() => self::getGenerator(['hello', null, [], false, '', 0, 0.0, 'world']),
            $callback,
            [0 => 'hello', 7 => 'world'],
            null,
        ];

        $callback = static function ($value): bool {
            return is_int($value) === true && $value >= 1;
        };

        yield 'Simple array with ARRAY_FILTER_USE_KEY mode' => [
            fn() => ['hello', 'world'],
            $callback,
            [1 => 'world'], // should be [0 => 'hello', 1 => 'world']
            ARRAY_FILTER_USE_KEY,
        ];

        yield 'Simple iterator with ARRAY_FILTER_USE_KEY mode' => [
            fn() => self::getGenerator(['hello', 'world']),
            $callback,
            [1 => 'world'], // should be [0 => 'hello', 1 => 'world']
            ARRAY_FILTER_USE_KEY,
        ];

        $callback = static function ($value, int $key): bool {
            return is_string($value) === true && str_contains($value, 'o') && $key >= 1;
        };

        yield 'Simple array with ARRAY_FILTER_USE_BOTH mode' => [
            fn() => ['hello', null, [], false, '', 0, 0.0, 'mister', 'world'],
            $callback,
            [8 => 'world'],
            ARRAY_FILTER_USE_BOTH,
        ];

        yield 'Simple iterator with ARRAY_FILTER_USE_BOTH mode' => [
            fn() => ['hello', null, [], false, '', 0, 0.0, 'mister', 'world'],
            $callback,
            [8 => 'world'],
            ARRAY_FILTER_USE_BOTH,
        ];
    }

    /**
     * @covers       filter
     *
     * @dataProvider dataWithCallback
     */
    public function testSuccessfulWithCallback(callable $iteratorFactory, ?callable $callback, array $expectedResult, ?int $mode): void
    {
        $iterator       = $iteratorFactory();
        $resultIterator = filter(
            $iterator,
            callback: $callback,
            mode: $mode ?: 0
        );

        self::assertIsIterable($resultIterator);
        self::assertIsNotArray($resultIterator);

        $resultArray = iterator_to_array($resultIterator);

        self::assertEquals($expectedResult, $resultArray);
    }

    /**
     * @covers       filter
     *
     * @dataProvider dataWithCallback
     */
    public function testSameAsNativeWithCallback(callable $iteratorFactory, ?callable $callback, array $expectedResult, ?int $mode): void
    {
        $iterator       = $iteratorFactory();
        $resultIterator = filter(
            $iterator,
            callback: $callback,
            mode: $mode ?: 0
        );

        $resultArray = iterator_to_array($resultIterator);

        $iterator = $iteratorFactory();
        self::assertEquals(
            array_filter(
                is_array($iterator) === true ? $iterator : iterator_to_array($iterator),
                callback: $callback,
                mode: $mode ?: 0
            ),
            $resultArray
        );
    }
}
