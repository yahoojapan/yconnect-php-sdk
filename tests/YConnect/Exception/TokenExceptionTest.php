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

namespace YConnect\Exception;

use PHPUnit_Framework_TestCase;

class TokenExceptionTest extends PHPUnit_Framework_TestCase
{
    private static $INVALID_REDIRECT_URI = "invalid_redirect_uri";
    private static $INVALID_CLIENT = "invalid_client";
    private static $INVALID_SCOPE = "invalid_scope";
    private static $INVALID_GRANT = "invalid_grant";
    private static $INVALID_TOKEN = "invalid_token";
    private static $INVALID_REQUEST = "invalid_request";
    private static $UNSUPPORTED_GRANT_TYPE = "unsupported_grant_type";
    private static $ACCESS_DENIED = "access_denied";
    private static $SERVER_ERROR = "server_error";

    /**
     * @test
     */
    public function testInvalidRedirectUriReturnsTrue()
    {
        $exception = new TokenException(self::$INVALID_REDIRECT_URI);

        $this->assertTrue($exception->invalidRedirectUri());
    }

    /**
     * @test
     */
    public function testInvalidRedirectUriReturnsFalse()
    {
        $exception = new TokenException(self::$INVALID_CLIENT);

        $this->assertFalse($exception->invalidRedirectUri());
    }

    /**
     * @test
     */
    public function testInvalidClientReturnsTrue()
    {
        $exception = new TokenException(self::$INVALID_CLIENT);

        $this->assertTrue($exception->invalidClient());
    }

    /**
     * @test
     */
    public function testInvalidClientReturnsFalse()
    {
        $exception = new TokenException(self::$INVALID_SCOPE);

        $this->assertFalse($exception->invalidClient());
    }

    /**
     * @test
     */
    public function testInvalidScopeReturnsTrue()
    {
        $exception = new TokenException(self::$INVALID_SCOPE);

        $this->assertTrue($exception->invalidScope());
    }

    /**
     * @test
     */
    public function testInvalidScopeReturnsFalse()
    {
        $exception = new TokenException(self::$INVALID_GRANT);

        $this->assertFalse($exception->invalidScope());
    }

    /**
     * @test
     */
    public function testInvalidGrantReturnsTrue()
    {
        $exception = new TokenException(self::$INVALID_GRANT);

        $this->assertTrue($exception->invalidGrant());
    }

    /**
     * @test
     */
    public function testInvalidGrantReturnsFalse()
    {
        $exception = new TokenException(self::$INVALID_TOKEN);

        $this->assertFalse($exception->invalidGrant());
    }

    /**
     * @test
     */
    public function testTokenExpiredReturnsTrue()
    {
        $exception = new TokenException(self::$INVALID_TOKEN);

        $this->assertTrue($exception->tokenExpired());
    }

    /**
     * @test
     */
    public function testTokenExpiredReturnsFalse()
    {
        $exception = new TokenException(self::$INVALID_REQUEST);

        $this->assertFalse($exception->tokenExpired());
    }

    /**
     * @test
     */
    public function testInvalidTokenReturnsTrue()
    {
        $exception = new TokenException(self::$INVALID_TOKEN);

        $this->assertTrue($exception->invalidToken());
    }

    /**
     * @test
     */
    public function testInvalidTokenReturnsFalse()
    {
        $exception = new TokenException(self::$INVALID_REQUEST);

        $this->assertFalse($exception->invalidToken());
    }

    /**
     * @test
     */
    public function testInvalidRequestReturnsTrue()
    {
        $exception = new TokenException(self::$INVALID_REQUEST);

        $this->assertTrue($exception->invalidRequest());
    }

    /**
     * @test
     */
    public function testInvalidRequestReturnsFalse()
    {
        $exception = new TokenException(self::$UNSUPPORTED_GRANT_TYPE);

        $this->assertFalse($exception->invalidRequest());
    }

    /**
     * @test
     */
    public function testUnsupportedGrantTypeReturnsTrue()
    {
        $exception = new TokenException(self::$UNSUPPORTED_GRANT_TYPE);

        $this->assertTrue($exception->unsupportedGrantType());
    }

    /**
     * @test
     */
    public function testUnsupportedGrantTypeReturnsFalse()
    {
        $exception = new TokenException(self::$ACCESS_DENIED);

        $this->assertFalse($exception->unsupportedGrantType());
    }

    /**
     * @test
     */
    public function testAccessDeniedReturnsTrue()
    {
        $exception = new TokenException(self::$ACCESS_DENIED);

        $this->assertTrue($exception->accessDenied());
    }

    /**
     * @test
     */
    public function testAccessDeniedReturnsFalse()
    {
        $exception = new TokenException(self::$SERVER_ERROR);

        $this->assertFalse($exception->accessDenied());
    }

    /**
     * @test
     */
    public function testServerErrorReturnsTrue()
    {
        $exception = new TokenException(self::$SERVER_ERROR);

        $this->assertTrue($exception->serverError());
    }

    /**
     * @test
     */
    public function testServerErrorReturnsFalse()
    {
        $exception = new TokenException(self::$INVALID_REDIRECT_URI);

        $this->assertFalse($exception->serverError());
    }
}
