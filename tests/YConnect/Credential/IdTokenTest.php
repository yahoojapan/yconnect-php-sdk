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

namespace YConnect\Credential;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionException;
use stdClass;
use UnexpectedValueException;
use YConnect\Exception\IdTokenException;

class IdTokenTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @throws ReflectionException
     * @throws IdTokenException
     */
    public function testVerify()
    {
        $access_token = "sample_access_token";
        $nonce = "sample_nonce";
        $client_id = "sample_client_id";

        $id_token = new IdTokenMock();

        $generate_hash_method = (new ReflectionClass($id_token))->getMethod("generateHash");
        $generate_hash_method->setAccessible(true);
        $at_hash = $generate_hash_method->invoke($id_token, $access_token);

        $obj = new stdClass();
        $obj->iss = "https://auth.login.yahoo.co.jp/yconnect/v2";
        $obj->nonce = $nonce;
        $obj->aud = [$client_id];
        $obj->at_hash = $at_hash;
        $obj->exp = strtotime("+1 sec");
        $obj->iat = strtotime('-10 min +1 sec');

        $this->assertTrue(IdToken::verify($obj, $nonce, $client_id, $access_token));
    }

    /**
     * @test
     * @throws IdTokenException
     */
    public function testVerifyWithoutAtHash()
    {
        $access_token = "sample_access_token";
        $nonce = "sample_nonce";
        $client_id = "sample_client_id";

        $obj = new stdClass();
        $obj->iss = "https://auth.login.yahoo.co.jp/yconnect/v2";
        $obj->nonce = $nonce;
        $obj->aud = [$client_id];
        $obj->exp = strtotime("+1 sec");
        $obj->iat = strtotime('-10 min +1 sec');

        $this->assertTrue(IdToken::verify($obj, $nonce, $client_id, $access_token));
    }

    /**
     * @test
     * @dataProvider providerTestVerifyThrowsIdTokenExceptionWhenIssuerInvalid
     * @throws IdTokenException
     */
    public function testVerifyThrowsIdTokenExceptionWhenIssuerInvalid($iss)
    {
        $access_token = "sample_access_token";
        $nonce = "sample_nonce";
        $client_id = "sample_client_id";

        $obj = new stdClass();
        $obj->iss = $iss;
        $obj->nonce = $nonce;
        $obj->aud = [$client_id];
        $obj->exp = strtotime("+1 sec");
        $obj->iat = strtotime('-10 min +1 sec');

        $this->expectException(IdTokenException::class);
        $this->expectExceptionMessage('Invalid issuer.');

        IdToken::verify($obj, $nonce, $client_id, $access_token);
    }

    public function providerTestVerifyThrowsIdTokenExceptionWhenIssuerInvalid()
    {
        return [
            "invalid issuer" => ["https://example.co.jp/invalid"],
            "YConnect v1 issuer" => ["https://auth.login.yahoo.co.jp"]
        ];
    }

    /**
     * @test
     * @throws IdTokenException
     */
    public function testVerifyThrowsIdTokenExceptionWhenNonceInvalid()
    {
        $access_token = "sample_access_token";
        $nonce = "sample_nonce";
        $client_id = "sample_client_id";

        $obj = new stdClass();
        $obj->iss = "https://auth.login.yahoo.co.jp/yconnect/v2";
        $obj->nonce = 'invalid_nonce';
        $obj->aud = [$client_id];
        $obj->exp = strtotime("+1 sec");
        $obj->iat = strtotime('-10 min +1 sec');

        $this->expectException(IdTokenException::class);
        $this->expectExceptionMessage('Not match nonce.');

        IdToken::verify($obj, $nonce, $client_id, $access_token);
    }

    /**
     * @test
     * @throws IdTokenException
     */
    public function testVerifyThrowsIdTokenExceptionWhenAudienceInvalid()
    {
        $access_token = "sample_access_token";
        $nonce = "sample_nonce";
        $client_id = "sample_client_id";

        $obj = new stdClass();
        $obj->iss = "https://auth.login.yahoo.co.jp/yconnect/v2";
        $obj->nonce = $nonce;
        $obj->aud = ["invalid_client_id"];
        $obj->exp = strtotime("+1 sec");
        $obj->iat = strtotime('-10 min +1 sec');

        $this->expectException(IdTokenException::class);
        $this->expectExceptionMessage('Invalid audience.');

        IdToken::verify($obj, $nonce, $client_id, $access_token);
    }

    /**
     * @test
     * @throws IdTokenException
     */
    public function testVerifyThrowsIdTokenExceptionWhenAtHashInvalid()
    {
        $access_token = "sample_access_token";
        $nonce = "sample_nonce";
        $client_id = "sample_client_id";

        $obj = new stdClass();
        $obj->iss = "https://auth.login.yahoo.co.jp/yconnect/v2";
        $obj->nonce = $nonce;
        $obj->aud = [$client_id];
        $obj->at_hash = 'invalid_at_hash';
        $obj->exp = strtotime("+1 sec");
        $obj->iat = strtotime('-10 min +1 sec');

        $this->expectException(IdTokenException::class);
        $this->expectExceptionMessage('Invalid at_hash.');

        IdToken::verify($obj, $nonce, $client_id, $access_token);
    }

    /**
     * @test
     * @throws IdTokenException
     */
    public function testVerifyThrowsIdTokenExceptionWhenIdTokenExpired()
    {
        $access_token = "sample_access_token";
        $nonce = "sample_nonce";
        $client_id = "sample_client_id";

        $obj = new stdClass();
        $obj->iss = "https://auth.login.yahoo.co.jp/yconnect/v2";
        $obj->nonce = $nonce;
        $obj->aud = [$client_id];
        $obj->exp = strtotime("-1 sec");
        $obj->iat = strtotime('-10 min +1 sec');

        $this->expectException(IdTokenException::class);
        $this->expectExceptionMessage('Expired ID Token.');

        IdToken::verify($obj, $nonce, $client_id, $access_token);
    }

    /**
     * @test
     * @throws IdTokenException
     */
    public function testVerifyThrowsIdTokenExceptionWhenOverAcceptableRange()
    {
        $access_token = "sample_access_token";
        $nonce = "sample_nonce";
        $client_id = "sample_client_id";

        $obj = new stdClass();
        $obj->iss = "https://auth.login.yahoo.co.jp/yconnect/v2";
        $obj->nonce = $nonce;
        $obj->aud = [$client_id];
        $obj->exp = strtotime("+1 sec");
        $obj->iat = strtotime('-10 min -1 sec');

        $this->expectException(IdTokenException::class);
        $this->expectExceptionMessage('Over acceptable range.');

        IdToken::verify($obj, $nonce, $client_id, $access_token);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testCheckFormat()
    {
        $json = new stdClass();
        $json->iss = "https://example.co.jp";
        $json->sub = "sample_ppid";
        $json->aud = ['sample_client_id'];
        $json->exp = 1635638400;
        $json->iat = 1635638400;
        $json->nonce = "sample_nonce";

        $id_token = new IdTokenMock();

        $check_format_method = (new ReflectionClass($id_token))->getMethod("checkFormat");
        $check_format_method->setAccessible(true);

        $this->assertNull($check_format_method->invoke($id_token, $json));
    }

    /**
     * @test
     *
     * @throws ReflectionException
     */
    public function testCheckFormatThrowsUnexpectedValueException()
    {
        $json = new stdClass();
        $json->iss = "https://example.co.jp";
        $json->sub = "sample_ppid";
        $json->aud = ['sample_client_id'];
        $json->exp = 1635638400;
        $json->iat = 1635638400;

        $id_token = new IdTokenMock();

        $check_format_method = (new ReflectionClass($id_token))->getMethod("checkFormat");
        $check_format_method->setAccessible(true);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Not a valid IdToken format');

        $check_format_method->invoke($id_token, $json);
    }
}

class IdTokenMock extends IdToken
{

    public function __construct()
    {
    }
}
