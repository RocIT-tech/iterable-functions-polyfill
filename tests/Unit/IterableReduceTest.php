<?php

declare(strict_types=1);

namespace RocIT\Tests\Unit;

use Generator;
use PHPUnit\Framework\TestCase;
use function array_reduce;
use function is_array;
use function iterator_to_array;
use function RocIT\reduce;

final class IterableReduceTest extends TestCase
{
    use IterableMocker;

    public function simpleData(): Generator
    {
        $callback = static function (?array $result, mixed $item): array {
            $result ??= [];

            if (empty($item) === false) {
                $result[] = $item;
            }

            return $result;
        };

        yield 'Simple array' => [
            fn() => ['hello', null, [], false, '', 0, 0.0, 'world'],
            [0 => 'hello', 1 => 'world'],
            $callback,
            null,
        ];

        yield 'Simple iterator' => [
            fn() => self::getGenerator(['hello', null, [], false, '', 0, 0.0, 'world']),
            [0 => 'hello', 1 => 'world'],
            $callback,
            null,
        ];
    }

    /**
     * @covers       ::reduce
     *
     * @dataProvider simpleData
     */
    public function testSuccessfulWithSimpleUseCases(callable $iteratorFactory, array $expectedResult, callable $callback, mixed $initialValue): void
    {
        $iterator    = $iteratorFactory();
        $resultArray = reduce(
            $iterator,
            $callback,
            $initialValue
        );

        self::assertIsIterable($resultArray);
        self::assertIsArray($resultArray);

        self::assertEquals($expectedResult, $resultArray);
    }

    /**
     * @covers       ::reduce
     *
     * @dataProvider simpleData
     */
    public function testSameAsNativeWithSimpleUseCases(callable $iteratorFactory, array $expectedResult, callable $callback, mixed $initialValue): void
    {
        $iterator    = $iteratorFactory();
        $resultArray = reduce(
            $iterator,
            $callback,
            $initialValue
        );

        $iterator = $iteratorFactory();
        self::assertEquals(
            array_reduce(
                is_array($iterator) === true ? $iterator : iterator_to_array($iterator),
                $callback,
                $initialValue
            ),
            $resultArray
        );
    }

    public function iteratorData(): Generator
    {
        $callback = static function (?Generator $result, mixed $item): Generator {
            if (null !== $result) {
                yield from $result;
            }

            if (empty($item) === false) {
                yield $item;
            }
        };

        yield 'Simple array' => [
            fn() => ['hello', null, [], false, '', 0, 0.0, 'world'],
            ['world'],
            $callback,
            null,
        ];

        yield 'Simple iterator' => [
            fn() => self::getGenerator(['hello', null, [], false, '', 0, 0.0, 'world']),
            ['world'],
            $callback,
            null,
        ];
    }

    /**
     * @covers       ::reduce
     *
     * @dataProvider iteratorData
     */
    public function testSuccessfulWithIterators(callable $iteratorFactory, array $expectedResult, callable $callback, mixed $initialValue): void
    {
        $iterator       = $iteratorFactory();
        $resultIterator = reduce(
            $iterator,
            $callback,
            $initialValue
        );

        self::assertIsIterable($resultIterator);

        $resultArray = (is_array($resultIterator) === false) ? iterator_to_array($resultIterator) : $resultIterator;

        self::assertEquals($expectedResult, $resultArray);
    }

    /**
     * @covers       ::reduce
     *
     * @dataProvider iteratorData
     */
    public function testSameAsNativeWithIterators(callable $iteratorFactory, array $expectedResult, callable $callback, mixed $initialValue): void
    {
        $iterator       = $iteratorFactory();
        $resultIterator = reduce(
            $iterator,
            $callback,
            $initialValue
        );

        $iterator = $iteratorFactory();
        self::assertEquals(
            array_reduce(
                is_array($iterator) === true ? $iterator : iterator_to_array($iterator),
                $callback,
                $initialValue
            ),
            $resultIterator
        );
    }
}
