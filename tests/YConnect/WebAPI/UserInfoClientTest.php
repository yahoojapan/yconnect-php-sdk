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

namespace YConnect\WebAPI;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionException;
use YConnect\Exception\ApiException;
use YConnect\Util\Logger;

class UserInfoClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @throws ReflectionException
     */
    public function testGetUserInfo()
    {
        $user_info = (object)array(
            "sub" => "sample_ppid",
            "name" => "sample_name"
        );

        $client = new UserInfoClientMock();

        $user_info_filed = (new ReflectionClass(UserInfoClient::class))->getProperty("user_info");
        $user_info_filed->setAccessible(true);
        $user_info_filed->setValue($client, $user_info);

        $this->assertEquals($user_info, $client->getUserInfo());
    }

    /**
     * @test
     */
    public function testGetUserInfoReturnsFalse()
    {
        $client = new UserInfoClientMock();

        $this->assertFalse($client->getUserInfo());
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testParseJson()
    {
        $sub = "sample_sub";
        $name = "sample_name";

        $client = new UserInfoClientMock();

        $json = json_encode(array(
            "sub" => $sub,
            "name" => $name
        ));

        $parse_json_method = (new ReflectionClass(UserInfoClient::class))->getMethod("parseJson");
        $parse_json_method->setAccessible(true);
        $parse_json_method->invoke($client, $json);

        $this->assertSame($sub, $client->getUserInfo()["sub"]);
        $this->assertSame($name, $client->getUserInfo()["name"]);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testParseJsonThrowsApiExceptionApiExceptionByErrorResponse()
    {
        // 実行画面にエラーログが表示されるのを防止
        Logger::setLogType(Logger::LOG_TYPE);
        Logger::setLogPath("/dev/null");

        $error = "page_not_found";
        $error_description = "/info is not found";
        $error_code = 404;

        $client = new UserInfoClientMock();

        $json = json_encode(array(
            "error" => $error,
            "error_description" => $error_description,
            "error_code" => $error_code
        ));

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage($error);

        $parse_json_method = (new ReflectionClass(UserInfoClient::class))->getMethod("parseJson");
        $parse_json_method->setAccessible(true);
        $parse_json_method->invoke($client, $json);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testParseJsonThrowsApiExceptionApiExceptionByNoResponse()
    {
        // 実行画面にエラーログが表示されるのを防止
        Logger::setLogType(Logger::LOG_TYPE);
        Logger::setLogPath("/dev/null");


        $client = new UserInfoClientMock();

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage("no_response");

        $parse_json_method = (new ReflectionClass(UserInfoClient::class))->getMethod("parseJson");
        $parse_json_method->setAccessible(true);
        $parse_json_method->invoke($client, null);
    }
}

class UserInfoClientMock extends UserInfoClient
{
    public function __construct()
    {
    }
}
