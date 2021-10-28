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

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionException;
use UnexpectedValueException;
use YConnect\Exception\TokenException;
use YConnect\Util\HttpClient;

class ApiClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @throws ReflectionException
     */
    public function testSetParams()
    {
        $client = new ApiClientMock();

        $set_params_method = (new ReflectionClass(ApiClient::class))->getMethod('setParams');
        $set_params_method->setAccessible(true);

        $params_field = (new ReflectionClass(ApiClient::class))->getProperty('params');
        $params_field->setAccessible(true);

        $key1 = 'key1';
        $key2 = 'key2';
        $val1 = 'val1';
        $val2 = 'val2';

        $set_params_method->invoke($client, array(
            $key1 => $val1,
            $key2 => $val2
        ));

        $params = $params_field->getValue($client);

        $this->assertSame($val1, $params[$key1]);
        $this->assertSame($val2, $params[$key2]);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testSetParamsThrowsUnexpectedValueException()
    {
        $client = new ApiClientMock();

        $set_params_method = (new ReflectionClass(ApiClient::class))->getMethod('setParams');
        $set_params_method->setAccessible(true);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('array is required');

        $set_params_method->invoke($client, 'invalid');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testSetParam()
    {
        $client = new ApiClientMock();

        $set_param_method = (new ReflectionClass(ApiClient::class))->getMethod('setParam');
        $set_param_method->setAccessible(true);

        $params_field = (new ReflectionClass(ApiClient::class))->getProperty('params');
        $params_field->setAccessible(true);

        $key = 'key';
        $val = 'val';

        $set_param_method->invoke($client, $key, $val);

        $params = $params_field->getValue($client);

        $this->assertSame($val, $params[$key]);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testSetParamGivenNumericKeyThenParamNotSet()
    {
        $client = new ApiClientMock();

        $set_param_method = (new ReflectionClass(ApiClient::class))->getMethod('setParam');
        $set_param_method->setAccessible(true);

        $params_field = (new ReflectionClass(ApiClient::class))->getProperty('params');
        $params_field->setAccessible(true);

        $key = 1234;
        $val = 'val';

        $set_param_method->invoke($client, $key, $val);

        $params = $params_field->getValue($client);

        $this->assertFalse(isset($params[$key]));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testSetParamGivenNotScalarValueThenParamNotSet()
    {
        $client = new ApiClientMock();

        $set_param_method = (new ReflectionClass(ApiClient::class))->getMethod('setParam');
        $set_param_method->setAccessible(true);

        $params_field = (new ReflectionClass(ApiClient::class))->getProperty('params');
        $params_field->setAccessible(true);

        $key = 'key';
        $val = array('key' => 'val');

        $set_param_method->invoke($client, $key, $val);

        $params = $params_field->getValue($client);

        $this->assertFalse(isset($params[$key]));
    }

    /**
     * @test
     * @dataProvider providerFetchResource
     * @throws ReflectionException
     */
    public function testFetchResourceGivenGetMethod($method, $expects)
    {
        $res = 'test - ok';

        $http_client = $this->createMock(HttpClient::class);
        $http_client->expects($expects[0])
            ->method('requestGet');
        $http_client->expects($expects[1])
            ->method('requestPost');
        $http_client->expects($expects[2])
            ->method('requestPut');
        $http_client->expects($expects[3])
            ->method('requestDelete');
        $http_client->expects($this->once())
            ->method('getResponseHeader')
            ->willReturn(null);
        $http_client->expects($this->once())
            ->method('getResponseBody')
            ->willReturn($res);

        $client = new ApiClientMock();
        $client->http_client = $http_client;

        $fetch_resource_method = (new ReflectionClass(ApiClient::class))->getMethod('fetchResource');
        $fetch_resource_method->setAccessible(true);
        $getLast_response_method = (new ReflectionClass(ApiClient::class))->getMethod('getLastResponse');
        $getLast_response_method->setAccessible(true);

        $fetch_resource_method->invoke($client, 'https://example.co.jp', $method);

        $this->assertSame($res, $getLast_response_method->invoke($client));
    }

    public function providerFetchResource()
    {
        return [
            'Given GET method' => ['GET', [$this->once(), $this->never(), $this->never(), $this->never()]],
            'Given POST method' => ['POST', [$this->never(), $this->once(), $this->never(), $this->never()]],
            'Given PUT method' => ['PUT', [$this->never(), $this->never(), $this->once(), $this->never()]],
            'Given DELETE method' => ['DELETE', [$this->never(), $this->never(), $this->never(), $this->once()]]
        ];
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testFetchResourceGivenUndefinedMethod()
    {
        $http_client = $this->createMock(HttpClient::class);
        $http_client->expects($this->never())
            ->method('requestGet');
        $http_client->expects($this->never())
            ->method('requestPost');
        $http_client->expects($this->never())
            ->method('requestPut');
        $http_client->expects($this->never())
            ->method('requestDelete');
        $http_client->expects($this->never())
            ->method('getResponseHeader');
        $http_client->expects($this->never())
            ->method('getResponseBody');

        $client = new ApiClientMock();
        $client->http_client = $http_client;

        $fetch_resource_method = (new ReflectionClass(ApiClient::class))->getMethod('fetchResource');
        $fetch_resource_method->setAccessible(true);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('unsupported http method');

        $fetch_resource_method->invoke($client, 'https://example.co.jp', 'UNDEFINED');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testFetchResourceThrowsUnexpectedValueExceptionWhenAuthorizationErrorGiven()
    {
        $error = 'sample error message';

        $http_client = $this->createMock(HttpClient::class);
        $http_client->expects($this->once())
            ->method('requestGet');
        $http_client->expects($this->never())
            ->method('requestPost');
        $http_client->expects($this->never())
            ->method('requestPut');
        $http_client->expects($this->never())
            ->method('requestDelete');
        $http_client->expects($this->once())
            ->method('getResponseHeader')
            ->willReturn($error);
        $http_client->expects($this->never())
            ->method('getResponseBody');

        $client = new ApiClientMock();
        $client->http_client = $http_client;

        $fetch_resource_method = (new ReflectionClass(ApiClient::class))->getMethod('fetchResource');
        $fetch_resource_method->setAccessible(true);

        $this->expectException(TokenException::class);
        $this->expectExceptionMessage($error);

        $fetch_resource_method->invoke($client, 'https://example.co.jp', 'GET');
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testCheckAuthorizationError()
    {
        $client = new ApiClientMock();

        $check_authorization_error_method = (new ReflectionClass(ApiClient::class))
            ->getMethod('checkAuthorizationError');
        $check_authorization_error_method->setAccessible(true);

        $this->assertNull($check_authorization_error_method->invoke($client, null));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testCheckAuthorizationErrorThrowsTokenException()
    {
        $error = 'sample error message';

        $client = new ApiClientMock();

        $check_authorization_error_method = (new ReflectionClass(ApiClient::class))
            ->getMethod('checkAuthorizationError');
        $check_authorization_error_method->setAccessible(true);

        $this->expectException(TokenException::class);
        $this->expectExceptionMessage($error);

        $check_authorization_error_method->invoke($client, $error);
    }
}

class ApiClientMock extends ApiClient
{
    public $http_client;

    public function __construct()
    {
    }

    protected function getHttpClient()
    {
        return $this->http_client;
    }
}
