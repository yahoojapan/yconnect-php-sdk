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

class ClientCredentialTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function testToAuthorizationHeader()
    {
        $client_id = "sample_client_id";
        $client_secret = "sample_client_secret";

        $credential = new ClientCredential($client_id, $client_secret);

        $expect = "c2FtcGxlX2NsaWVudF9pZDpzYW1wbGVfY2xpZW50X3NlY3JldA==";
        $this->assertSame($expect, $credential->toAuthorizationHeader());
    }

    /**
     * @test
     */
    public function testToQueryString()
    {
        $client_id = "sample~client~id";
        $client_secret = "sample~client~secret";

        $credential = new ClientCredential($client_id, $client_secret);

        $expect = "client_id=sample%7Eclient%7Eid&client_secret=sample%7Eclient%7Esecret";
        $this->assertSame($expect, $credential->toQueryString());
    }
}
