<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (C) 2021 Yahoo Japan Corporation. All Rights Reserved.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace YConnect\Util;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionException;

class HttpClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @throws ReflectionException
     */
    public function testGetResponseHeaders()
    {
        $key = "WWW-Authenticate";
        $val = "unauthorized_error";
        $headers = array(
            $key => $val
        );

        $client = new HttpClientMock();

        $headers_field = (new ReflectionClass(HttpClient::class))->getProperty('headers');
        $headers_field->setAccessible(true);
        $headers_field->setValue($client, $headers);

        $this->assertSame($val, $client->getResponseHeaders()[$key]);
    }

    /**
     * @test
     */
    public function testGetResponseHeadersReturnsEmptyArray()
    {
        $client = new HttpClientMock();

        $result = $client->getResponseHeaders();
        $this->assertTrue(is_array($result));
        $this->assertEmpty($result);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testGetResponseHeader()
    {
        $key = "WWW-Authenticate";
        $val = "unauthorized_error";
        $headers = array(
            $key => $val
        );

        $client = new HttpClientMock();

        $headers_field = (new ReflectionClass(HttpClient::class))->getProperty('headers');
        $headers_field->setAccessible(true);
        $headers_field->setValue($client, $headers);

        $this->assertSame($val, $client->getResponseHeader($key));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testGetResponseHeaderReturnsNull()
    {
        $key = "WWW-Authenticate";
        $val = "unauthorized_error";
        $headers = array(
            $key => $val
        );

        $client = new HttpClientMock();

        $headers_field = (new ReflectionClass(HttpClient::class))->getProperty('headers');
        $headers_field->setAccessible(true);
        $headers_field->setValue($client, $headers);

        $this->assertNull($client->getResponseHeader("invalid_key"));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testGetResponseBody()
    {
        $body = "200 - OK";

        $client = new HttpClientMock();

        $body_field = (new ReflectionClass(HttpClient::class))->getProperty('body');
        $body_field->setAccessible(true);
        $body_field->setValue($client, $body);

        $this->assertSame($body, $client->getResponseBody());
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testGetResponseBodyReturnsNull()
    {
        $client = new HttpClientMock();

        $body_field = (new ReflectionClass(HttpClient::class))->getProperty('body');
        $body_field->setAccessible(true);
        $body_field->setValue($client, null);

        $this->assertNull($client->getResponseBody());
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testExtractResponse()
    {
        $header = "HTTP/1.1 200 OK\r\nAccept-Ranges: none\r\n\r\n";
        $body = "200 - OK\r\n";

        $info = array(
            "header_size" => strlen($header)
        );

        $client = new HttpClientMock();

        $extract_response_method = (new ReflectionClass(HttpClient::class))->getMethod('extractResponse');
        $extract_response_method->setAccessible(true);
        $extract_response_method->invoke($client, $header . $body, $info);

        $this->assertSame("HTTP/1.1 200 OK", $client->getResponseHeaders()[0]);
        $this->assertSame("none", $client->getResponseHeaders()["Accept-Ranges"]);
        $this->assertSame($body, $client->getResponseBody());
    }
}

class HttpClientMock extends HttpClient
{
    public function __construct()
    {
    }
}
