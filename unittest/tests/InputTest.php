<?php

declare(strict_types=1);

use orange\framework\Input;
use orange\framework\exceptions\InvalidValue;

final class InputTest extends unitTestHelper
{
    protected $instance;

    protected $default = [
        'query' => [
            'name' => 'Jenny Appleseed',
            'age' => 25,
        ],
        'files' => [],
        'server' => [
            'request_uri' => '/product/123abc',
            'request_method' => 'get',
            'http_x_requested_with' => 'xmlhttprequest',
            'https' => 'on',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7,fr;q=0.6,el;q=0.5',
        ],
        'request' => [
            'name' => 'Jon Appleseed',
            'age' => 26,
        ],
        'cookies' => [
            'name' => 'James Appleseed',
            'age' => 28,
        ],

        // looks like a apache server request
        'php_sapi' => 'APACHE',
        'stdin' => false,
    ];

    protected function setUp(): void
    {
        $this->instance = Input::getInstance($this->default);
    }

    // Tests
    public function testGetUrl(): void
    {
        $this->assertEquals('/product/123abc', $this->instance->GetUrl());
        $this->assertEquals('/product/123abc', $this->instance->GetUrl(Input::SCHEME));
        $this->assertEquals(null, $this->instance->GetUrl(Input::HOST));
        $this->assertEquals(null, $this->instance->GetUrl(Input::PORT));
        $this->assertEquals(null, $this->instance->GetUrl(Input::USER));
        $this->assertEquals(null, $this->instance->GetUrl(Input::PASS));
        $this->assertEquals('/product/123abc', $this->instance->GetUrl(Input::PATH));
        $this->assertEquals(null, $this->instance->GetUrl(Input::QUERY));
        $this->assertEquals(null, $this->instance->GetUrl(Input::FRAGMENT));
    }

    public function testRequestUri(): void
    {
        $this->assertEquals('/product/123abc', $this->instance->requestUri());
    }

    public function testUriSegment(): void
    {
        $this->assertEquals('', $this->instance->uriSegment(0));
        $this->assertEquals('product', $this->instance->uriSegment(1));
        $this->assertEquals('123abc', $this->instance->uriSegment(2));
        $this->assertEquals('', $this->instance->uriSegment(3));
    }

    public function testContentType(): void
    {
        $this->assertEquals('', $this->instance->contentType());
    }

    public function testRequestMethod(): void
    {
        $this->assertEquals('get', $this->instance->requestMethod());
    }

    public function testRequestType(): void
    {
        $this->assertEquals('ajax', $this->instance->requestType());
    }

    public function testIsAjaxRequest(): void
    {
        $this->assertEquals(true, $this->instance->isAjaxRequest());
    }

    public function testIsCliRequest(): void
    {
        $this->assertEquals(false, $this->instance->isCliRequest());
    }

    public function testIsHttpsRequest(): void
    {
        $this->assertEquals(true, $this->instance->isHttpsRequest());
        $this->assertEquals('https', $this->instance->isHttpsRequest(true));
    }

    public function testRequest(): void
    {
        $this->assertEquals([
            'name' => 'Jon Appleseed',
            'age' => 26,
        ], $this->instance->request());
    }

    public function testQuery(): void
    {
        $this->assertEquals([
            'name' => 'Jenny Appleseed',
            'age' => 25,
        ], $this->instance->query());
    }

    public function testCookie(): void
    {
        $this->assertEquals([
            'name' => 'James Appleseed',
            'age' => 28,
        ], $this->instance->cookie());
    }

    public function testServer(): void
    {
        $this->assertEquals('get', $this->instance->server('request_method'));
        $this->assertEquals('en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7,fr;q=0.6,el;q=0.5', $this->instance->server('HTTP_ACCEPT_LANGUAGE'));
    }

    public function testHeader(): void
    {
        $this->assertEquals('en-US,en;q=0.9,zh-CN;q=0.8,zh;q=0.7,fr;q=0.6,el;q=0.5', $this->instance->header('HTTP_ACCEPT_LANGUAGE'));
    }

    public function testFile(): void
    {
        $this->assertEquals([], $this->instance->file(0));
    }

    public function testJsonRequest(): void
    {
        Input::destroyInstance();

        $instance = Input::getInstance([
            'inputStream' => '{"name": "Joe","age": 24}',
            'server' => ['content type' => 'application/json', 'request_method' => 'POST'],
        ]);

        $this->assertEquals([
            'name' => 'Joe',
            'age' => 24,
        ], $instance->request());
    }
}
