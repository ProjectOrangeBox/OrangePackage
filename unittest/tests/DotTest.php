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
        Dot::changeDelimiter('.');
        Dot::set($data, 'a.b', 'newvalue');
        $this->assertEquals('newvalue', Dot::get($data, 'a.b'));

        $this->assertEquals([
            'a.b' => 'newvalue',
        ], Dot::flatten($data));

        // Change delimiter to '-'
        Dot::changeDelimiter('-');
        Dot::set($data, 'a-b', 'dashvalue');
        $this->assertEquals('dashvalue', Dot::get($data, 'a-b'));

        $this->assertEquals([
            'a-b' => 'dashvalue',
        ], Dot::flatten($data));
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

        $this->assertEquals([
            'key' => 'value',
            'nested.key' => 'nestedvalue',
            'deep.nested.key' => 'deepvalue',
        ], Dot::flatten($data));
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

        // Deep nested
        Dot::set($data, 'deep.nested.key', 'deepvalue');
        $this->assertEquals('deepvalue', Dot::get($data, 'deep.nested.key'));

        $this->assertEquals([
            'key' => 'value',
            'nested.key' => 'nestedvalue',
            'deep.nested.key' => 'deepvalue',
        ], Dot::flatten($data));
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

        $this->assertEquals([
            'a.c' => 'othervalue',
        ], Dot::flatten($data));

        Dot::unset($data, 'a');
        $this->assertFalse(Dot::isset($data, 'a'));

        $this->assertEquals([], Dot::flatten($data));
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

        $this->assertEquals([
            'a' => 'value',
            'b.c' => 'nested',
            'b.d.e' => 'deep'
        ], Dot::flatten($data));
    }

    public function testExpandWithArray(): void
    {
        $data = [
            'a' => 'value',
            'b.c' => 'nested',
            'b.d.e' => 'deep'
        ];

        $this->assertEquals([
            'a' => 'value',
            'b' => [
                'c' => 'nested',
                'd' => [
                    'e' => 'deep'
                ]
            ]
        ], Dot::expand($data));
    }

    public function testExpandWithObject(): void
    {
        $data = new \StdClass();
        $data->{'a'} = 'value';
        $data->{'b.c'} = 'nested';
        $data->{'b.d.e'} = 'deep';

        $this->assertEquals([
            'a' => 'value',
            'b' => [
                'c' => 'nested',
                'd' => [
                    'e' => 'deep'
                ]
            ]
        ], Dot::expand($data));
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
