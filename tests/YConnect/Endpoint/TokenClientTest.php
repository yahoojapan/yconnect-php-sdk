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
use YConnect\Credential\ClientCredential;
use YConnect\Exception\TokenException;
use YConnect\Util\HttpClient;

class TokenClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @throws Exception
     */
    public function testFetchToken()
    {
        $access_token = "sample_access_token";
        $exp = 3600;
        $refresh_token = "sample_refresh_token";
        $id_token = "sample_id_token";

        $json = json_encode(array(
            "access_token" => $access_token,
            "expires_in" => $exp,
            "refresh_token" => $refresh_token,
            "id_token" => $id_token
        ));

        $client = new TokenClientMock();

        $http_client = $this->createMock(HttpClient::class);
        $http_client->expects($this::once())
            ->method('requestPost');
        $http_client->expects($this::once())
            ->method('getResponseBody')
            ->willReturn($json);

        $cred_filed = (new ReflectionClass(TokenClient::class))->getProperty("cred");
        $cred_filed->setAccessible(true);
        $cred_filed->setValue($client, new ClientCredential("client_id_sample", "client_secret_sample"));

        $client->httpClient = $http_client;

        $client->fetchToken();

        $this->assertSame($json, $client->getResponse());
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testGetResponse()
    {
        $access_token = "sample_access_token";
        $exp = 3600;
        $refresh_token = "sample_refresh_token";
        $id_token = "sample_id_token";

        $json = json_encode(array(
            "access_token" => $access_token,
            "expires_in" => $exp,
            "refresh_token" => $refresh_token,
            "id_token" => $id_token
        ));

        $client = new TokenClientMock();

        $res_body_filed = (new ReflectionClass(TokenClient::class))->getProperty("res_body");
        $res_body_filed->setAccessible(true);
        $res_body_filed->setValue($client, $json);

        $this->assertSame($json, $client->getResponse());
    }

    /**
     * @test
     */
    public function testGetResponseReturnsFalse()
    {
        $client = new TokenClientMock();

        $this->assertFalse($client->getResponse());
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testSetParams()
    {
        $key1 = 'key1';
        $key2 = 'key2';
        $val1 = 'val1';
        $val2 = 'val2';

        $client = new TokenClientMock();

        $client->setParam($key1, $val1);
        $client->setParams(array($key2 => $val2));

        $params_field = (new ReflectionClass(TokenClient::class))->getProperty('params');
        $params_field->setAccessible(true);

        $this->assertSame($val1, $params_field->getValue($client)[$key1]);
        $this->assertSame($val2, $params_field->getValue($client)[$key2]);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testSetParam()
    {
        $client = new TokenClientMock();

        $params_field = (new ReflectionClass(TokenClient::class))->getProperty('params');
        $params_field->setAccessible(true);

        $key = 'key';
        $val = 'val';

        $client->setParam($key, $val);

        $params = $params_field->getValue($client);

        $this->assertSame($val, $params[$key]);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testCheckErrorResponse()
    {
        $client = new TokenClientMock();

        $response = array(
            "access_token" => "access_token_sample",
            "expires_in" => 3600
        );

        $checkErrorResponseMethod = (new ReflectionClass(TokenClient::class))->getMethod('checkErrorResponse');
        $checkErrorResponseMethod->setAccessible(true);
        $checkErrorResponseMethod->invoke($client, $response);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testCheckErrorResponseThrowsTokenExceptionByErrorResponse()
    {
        $client = new TokenClientMock();

        $error = "error_sample";

        $response = array(
            "error" => $error,
            "error_description" => "error_description_sample",
            "error_code" => 1000
        );

        $this->expectException(TokenException::class);
        $this->expectExceptionMessage($error);

        $checkErrorResponseMethod = (new ReflectionClass(TokenClient::class))->getMethod('checkErrorResponse');
        $checkErrorResponseMethod->setAccessible(true);
        $checkErrorResponseMethod->invoke($client, $response);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testCheckErrorResponseThrowsTokenExceptionByNoResponse()
    {
        $client = new TokenClientMock();

        $this->expectException(TokenException::class);
        $this->expectExceptionMessage("no_response");

        $checkErrorResponseMethod = (new ReflectionClass(TokenClient::class))->getMethod('checkErrorResponse');
        $checkErrorResponseMethod->setAccessible(true);
        $checkErrorResponseMethod->invoke($client, null);
    }
}

class TokenClientMock extends TokenClient
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
