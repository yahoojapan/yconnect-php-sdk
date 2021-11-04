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

namespace YConnect\Endpoint;

use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionException;
use YConnect\Util\HttpClient;

class PublicKeysClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function testFetchPublicKeys()
    {
        $json = json_encode(array(
            "kid1" => "sample_public_key"
        ));

        $client = new PublicKeysClientMock();

        $http_client = $this->createMock(HttpClient::class);
        $http_client->expects($this::once())
            ->method('requestGet');
        $http_client->expects($this::once())
            ->method('getResponseBody')
            ->willReturn($json);

        $client->httpClient = $http_client;

        $client->fetchPublicKeys();

        $this->assertSame($json, $client->getResponse());
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testGetResponse()
    {
        $json = json_encode(array(
            "kid1" => "sample_public_key"
        ));

        $client = new PublicKeysClientMock();

        $res_body_filed = (new ReflectionClass(PublicKeysClient::class))->getProperty("res_body");
        $res_body_filed->setAccessible(true);
        $res_body_filed->setValue($client, $json);

        $this->assertSame($json, $client->getResponse());
    }

    /**
     * @test
     */
    public function testGetResponseReturnsFalse()
    {
        $client = new PublicKeysClientMock();

        $this->assertNull($client->getResponse());
    }
}

class PublicKeysClientMock extends PublicKeysClient
{
    public $httpClient;

    public function __construct()
    {
    }

    protected function getHttpClient()
    {
        return $this->httpClient;
    }
}
