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
use UnexpectedValueException;
use YConnect\Credential\PublicKeys;

class JWTTest extends PHPUnit_Framework_TestCase
{
    private static $kid = "sample_kid";
    private static $payload;

    private static $key_pair;
    private static $public_keys;

    public static function setUpBeforeClass()
    {
        self::$payload = array(
            "iss" => "https://auth.login.yahoo.co.jp/yconnect/v2",
            "sub" => "sample_ppid",
            "aud" => ["sample_client_id"],
            "exp" => strtotime("+1 day"),
            "iat" => strtotime('-9 min'),
            "nonce" => "sample_nonce",
            "at_hash" => "sample_at_hash"
        );

        self::$key_pair = openssl_pkey_new(array(
            'digest_alg' => 'sha256',
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ));
        $public_key_pem = openssl_pkey_get_details(self::$key_pair)['key'];

        self::$public_keys = new PublicKeys(json_encode(array(
            self::$kid => $public_key_pem
        )));
    }

    /**
     * @test
     */
    public function testGetDecodedToken()
    {
        $jwt = new \Ahc\Jwt\JWT([self::$kid => self::$key_pair], 'RS256');
        $token = $jwt->encode(self::$payload, ['kid' => self::$kid]);
        $result = JWT::getDecodedToken($token, self::$public_keys);

        $this->assertEquals((object) self::$payload, $result);
    }

    /**
     * @test
     */
    public function testGetDecodedTokenThrowsUnexpectedValueExceptionByInvalidJwtFormat()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("invalid jwt format");

        $jwt = "invalid.invalid";
        JWT::getDecodedToken($jwt, self::$public_keys);
    }

    /**
     * @test
     */
    public function testGetDecodedTokenThrowsUnexpectedValueExceptionByKidMissed()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("header does not have kid parameter");

        $jwt = new \Ahc\Jwt\JWT([self::$kid => self::$key_pair], 'RS256');
        $token = $jwt->encode(self::$payload);
        JWT::getDecodedToken($token, self::$public_keys);
    }

    /**
     * @test
     */
    public function testGetDecodedTokenThrowsUnexpectedValueExceptionByInvalidKid()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("public key for kid not found");

        $jwt = new \Ahc\Jwt\JWT(['invalid_kid' => self::$key_pair], 'RS256');
        $token = $jwt->encode(self::$payload, ['kid' => 'invalid_kid']);
        JWT::getDecodedToken($token, self::$public_keys);
    }

    /**
     * @test
     */
    public function testGetDecodedTokenThrowsUnexpectedValueExceptionByVerifySignatureFailed()
    {
        $key_pair = openssl_pkey_new(array(
            'digest_alg' => 'sha256',
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ));

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("invalid jwt signature");

        $jwt = new \Ahc\Jwt\JWT([self::$kid => $key_pair], 'RS256');
        $token = $jwt->encode(self::$payload, ['kid' => self::$kid]);
        JWT::getDecodedToken($token, self::$public_keys);
    }

    /**
     * @test
     */
    public function testUrlDecode()
    {
        $expect = "sample\n";
        $this->assertSame($expect, JWT::urlDecode("c2FtcGxlCg"));
    }

    /**
     * @test
     */
    public function testJsonDecode()
    {
        $expect = (object) array(
            "key1" => "value1",
            "key2" => "value2"
        );

        $this->assertEquals($expect, JWT::jsonDecode(json_encode($expect)));
    }

    /**
     * @test
     */
    public function testJsonDecodeThrowsUnexpectedValueException()
    {
        $json = json_encode(array(
            "key1" => "value1",
            "key2" => "value2"
        )) . "}";

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage("JSON decode error: " . JSON_ERROR_SYNTAX);

        JWT::jsonDecode($json);
    }
}
