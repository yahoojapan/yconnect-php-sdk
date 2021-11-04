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

class AuthorizationExceptionTest extends PHPUnit_Framework_TestCase
{
    private static $INVALID_REQUEST = "invalid_request";
    private static $INVALID_SCOPE = "invalid_scope";
    private static $LOGIN_REQUIRED = "login_required";
    private static $CONSENT_REQUIRED = "consent_required";
    private static $UNSUPPORTED_RESPONSE_TYPE = "unsupported_response_type";
    private static $UNAUTHORIZED_CLIENT = "unauthorized_client";
    private static $ACCESS_DENIED = "access_denied";
    private static $SERVER_ERROR = "server_error";

    /**
     * @test
     */
    public function testInvalidRequestReturnsTrue()
    {
        $exception = new AuthorizationException(self::$INVALID_REQUEST);

        $this->assertTrue($exception->invalidRequest());
    }

    /**
     * @test
     */
    public function testInvalidRequestReturnsFalse()
    {
        $exception = new AuthorizationException(self::$INVALID_SCOPE);

        $this->assertFalse($exception->invalidRequest());
    }

    /**
     * @test
     */
    public function testInvalidScopeReturnsTrue()
    {
        $exception = new AuthorizationException(self::$INVALID_SCOPE);

        $this->assertTrue($exception->invalidScope());
    }

    /**
     * @test
     */
    public function testInvalidScopeReturnsFalse()
    {
        $exception = new AuthorizationException(self::$LOGIN_REQUIRED);

        $this->assertFalse($exception->invalidScope());
    }

    /**
     * @test
     */
    public function testLoginRequiredReturnsTrue()
    {
        $exception = new AuthorizationException(self::$LOGIN_REQUIRED);

        $this->assertTrue($exception->loginRequired());
    }

    /**
     * @test
     */
    public function testLoginRequiredReturnsFalse()
    {
        $exception = new AuthorizationException(self::$CONSENT_REQUIRED);

        $this->assertFalse($exception->loginRequired());
    }

    /**
     * @test
     */
    public function testConsentRequiredReturnsTrue()
    {
        $exception = new AuthorizationException(self::$CONSENT_REQUIRED);

        $this->assertTrue($exception->consentRequired());
    }

    /**
     * @test
     */
    public function testConsentRequiredReturnsFalse()
    {
        $exception = new AuthorizationException(self::$UNSUPPORTED_RESPONSE_TYPE);

        $this->assertFalse($exception->consentRequired());
    }

    /**
     * @test
     */
    public function testUnsupportedResponseTypeReturnsTrue()
    {
        $exception = new AuthorizationException(self::$UNSUPPORTED_RESPONSE_TYPE);

        $this->assertTrue($exception->unsupportedResponseType());
    }

    /**
     * @test
     */
    public function testUnsupportedResponseTypeReturnsFalse()
    {
        $exception = new AuthorizationException(self::$UNAUTHORIZED_CLIENT);

        $this->assertFalse($exception->unsupportedResponseType());
    }

    /**
     * @test
     */
    public function testUnauthorizedClientReturnsTrue()
    {
        $exception = new AuthorizationException(self::$UNAUTHORIZED_CLIENT);

        $this->assertTrue($exception->unauthorizedClient());
    }

    /**
     * @test
     */
    public function testUnauthorizedClientReturnsFalse()
    {
        $exception = new AuthorizationException(self::$ACCESS_DENIED);

        $this->assertFalse($exception->unauthorizedClient());
    }

    /**
     * @test
     */
    public function testAccessDeniedReturnsTrue()
    {
        $exception = new AuthorizationException(self::$ACCESS_DENIED);

        $this->assertTrue($exception->accessDenied());
    }

    /**
     * @test
     */
    public function testAccessDeniedReturnsFalse()
    {
        $exception = new AuthorizationException(self::$SERVER_ERROR);

        $this->assertFalse($exception->accessDenied());
    }

    /**
     * @test
     */
    public function testServerErrorReturnsTrue()
    {
        $exception = new AuthorizationException(self::$SERVER_ERROR);

        $this->assertTrue($exception->serverError());
    }

    /**
     * @test
     */
    public function testServerErrorReturnsFalse()
    {
        $exception = new AuthorizationException(self::$INVALID_REQUEST);

        $this->assertFalse($exception->serverError());
    }
}
