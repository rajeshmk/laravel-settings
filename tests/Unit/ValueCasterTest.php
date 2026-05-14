<?php

declare(strict_types=1);

use Hatchyu\Settings\Resolvers\ValueCaster;

it('encodes and decodes scalar values', function (): void {
    expect(ValueCaster::decode('integer', '42'))->toBe(42)
        ->and(ValueCaster::decode('float', '10.5'))->toBe(10.5)
        ->and(ValueCaster::decode('boolean', '1'))->toBeTrue()
        ->and(ValueCaster::decode('boolean', '0'))->toBeFalse()
        ->and(ValueCaster::decode('string', 'hello'))->toBe('hello')
    ;
});

it('encodes boolean-like values consistently', function (): void {
    expect(ValueCaster::encode('boolean', true))->toBe('1')
        ->and(ValueCaster::encode('boolean', false))->toBe('0')
        ->and(ValueCaster::encode('boolean', 'true'))->toBe('1')
        ->and(ValueCaster::encode('boolean', 'false'))->toBe('0')
        ->and(ValueCaster::encode('boolean', '0'))->toBe('0')
    ;
});

it('encodes and decodes structured values', function (): void {
    $array = ['theme' => 'dark'];
    $object = (object) ['name' => 'Laravel'];

    expect(ValueCaster::decode('array', ValueCaster::encode('array', $array)))->toBe($array)
        ->and(ValueCaster::decode('json', ValueCaster::encode('json', $array)))->toBe($array)
        ->and(ValueCaster::decode('object', ValueCaster::encode('object', $object)))->toEqual($object)
    ;
});

it('keeps null values as null', function (): void {
    expect(ValueCaster::decode('string', null))->toBeNull();
});
