<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use orange\framework\helpers\Dot;

class DotTest extends TestCase
{
    public function testChangeDelimiter(): void
    {
        // Default delimiter is '.'
        $data = ['a' => ['b' => 'value']];
        Dot::set($data, 'a.b', 'newvalue');
        $this->assertEquals('newvalue', Dot::get($data, 'a.b'));

        // Change delimiter to '-'
        Dot::changeDelimiter('-');
        Dot::set($data, 'a-b', 'dashvalue');
        $this->assertEquals('dashvalue', Dot::get($data, 'a-b'));

        // Reset to default
        Dot::changeDelimiter('.');
    }

    public function testSetAndGetWithArray(): void
    {
        $data = [];

        // Simple key
        Dot::set($data, 'key', 'value');
        $this->assertEquals('value', Dot::get($data, 'key'));

        // Nested key
        Dot::set($data, 'nested.key', 'nestedvalue');
        $this->assertEquals('nestedvalue', Dot::get($data, 'nested.key'));
        $this->assertEquals(['key' => 'nestedvalue'], $data['nested']);

        // Deep nested
        Dot::set($data, 'deep.nested.key', 'deepvalue');
        $this->assertEquals('deepvalue', Dot::get($data, 'deep.nested.key'));
    }

    public function testSetAndGetWithObject(): void
    {
        $data = new \stdClass();

        // Simple key
        Dot::set($data, 'key', 'value');
        $this->assertEquals('value', Dot::get($data, 'key'));

        // Nested key
        Dot::set($data, 'nested.key', 'nestedvalue');
        $this->assertEquals('nestedvalue', Dot::get($data, 'nested.key'));
        $this->assertInstanceOf(\stdClass::class, $data->nested);
        $this->assertEquals('nestedvalue', $data->nested->key);
    }

    public function testGetWithDefault(): void
    {
        $data = ['a' => 'value'];

        $this->assertEquals('value', Dot::get($data, 'a', 'default'));
        $this->assertEquals('default', Dot::get($data, 'missing', 'default'));
        $this->assertEquals('default', Dot::get($data, 'missing.deep', 'default'));
    }

    public function testIsset(): void
    {
        $data = ['a' => ['b' => 'value']];

        $this->assertTrue(Dot::isset($data, 'a'));
        $this->assertTrue(Dot::isset($data, 'a.b'));
        $this->assertFalse(Dot::isset($data, 'missing'));
        $this->assertFalse(Dot::isset($data, 'a.missing'));
    }

    public function testUnset(): void
    {
        $data = ['a' => ['b' => 'value', 'c' => 'othervalue']];

        Dot::unset($data, 'a.b');
        $this->assertFalse(Dot::isset($data, 'a.b'));
        $this->assertTrue(Dot::isset($data, 'a.c'));

        Dot::unset($data, 'a');
        $this->assertFalse(Dot::isset($data, 'a'));
    }

    public function testFlatten(): void
    {
        $data = [
            'a' => 'value',
            'b' => [
                'c' => 'nested',
                'd' => [
                    'e' => 'deep'
                ]
            ]
        ];

        $flattened = Dot::flatten($data);
        $expected = [
            'a' => 'value',
            'b.c' => 'nested',
            'b.d.e' => 'deep'
        ];

        $this->assertEquals($expected, $flattened);
    }

    public function testExpand(): void
    {
        $data = [
            'a' => 'value',
            'b.c' => 'nested',
            'b.d.e' => 'deep'
        ];

        $expanded = Dot::expand($data);
        $expected = [
            'a' => 'value',
            'b' => [
                'c' => 'nested',
                'd' => [
                    'e' => 'deep'
                ]
            ]
        ];

        $this->assertEquals($expected, $expanded);
    }

    public function testRoundTripFlattenExpand(): void
    {
        $original = [
            'user' => [
                'name' => 'John',
                'profile' => [
                    'age' => 30,
                    'city' => 'NYC'
                ]
            ],
            'settings' => [
                'theme' => 'dark'
            ]
        ];

        $flattened = Dot::flatten($original);
        $expanded = Dot::expand($flattened);

        $this->assertEquals($original, $expanded);
    }
}