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
use YConnect\Credential\BearerToken;
use YConnect\Exception\TokenException;
use YConnect\Util\Logger;

class RefreshTokenClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @throws ReflectionException
     */
    public function testGetAccessToken()
    {
        $access_token = "sample_access_token";
        $exp = 3600;

        $client = new RefreshTokenClientMock();

        $access_token_filed = (new ReflectionClass(RefreshTokenClient::class))->getProperty("access_token");
        $access_token_filed->setAccessible(true);
        $access_token_filed->setValue($client, new BearerToken($access_token, $exp));

        $this->assertSame($access_token, $client->getAccessToken()->toAuthorizationHeader());
    }

    /**
     * @test
     */
    public function testGetAccessTokenReturnsFalse()
    {
        $client = new RefreshTokenClientMock();

        $this->assertFalse($client->getAccessToken());
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testParseJson()
    {
        $access_token = "sample_access_token";
        $exp = 3600;

        $client = new RefreshTokenClientMock();

        $json = json_encode(array(
            "access_token" => $access_token,
            "expires_in" => $exp
        ));

        $parse_json_method = (new ReflectionClass(RefreshTokenClient::class))->getMethod("parseJson");
        $parse_json_method->setAccessible(true);
        $parse_json_method->invoke($client, $json);

        $this->assertSame($access_token, $client->getAccessToken()->toAuthorizationHeader());
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testParseJsonThrowsTokenException()
    {
        // 実行画面にエラーログが表示されるのを防止
        Logger::setLogType(Logger::LOG_TYPE);
        Logger::setLogPath("/dev/null");

        $error = "error_sample";
        $error_description = "/info is not found";
        $error_code = 404;

        $client = new RefreshTokenClientMockForParseJson();

        $json = json_encode(array(
            "error" => $error,
            "error_description" => $error_description,
            "error_code" => $error_code
        ));

        $this->expectException(TokenException::class);
        $this->expectExceptionMessage($error);

        $parse_json_method = (new ReflectionClass(RefreshTokenClient::class))->getMethod("parseJson");
        $parse_json_method->setAccessible(true);
        $parse_json_method->invoke($client, $json);
    }
}

class RefreshTokenClientMock extends RefreshTokenClient
{

    public function __construct()
    {
    }
}

class RefreshTokenClientMockForParseJson extends RefreshTokenClient
{

    public function __construct()
    {
    }

    protected function checkErrorResponse($response)
    {
        throw new TokenException("error_sample", "", "");
    }
}
