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

class PublicKeysTest extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function testGetPublicKey()
    {
        $kid = 'sample_kid';
        $public_key = 'sample_public_key';

        $json = json_encode(array(
            $kid => $public_key
        ));

        $public_keys = new PublicKeys($json);

        $this->assertSame($public_key, $public_keys->getPublicKey($kid));
    }

    /**
     * @test
     */
    public function testGetPublicKeyReturnsNull()
    {
        $kid = 'sample_kid';
        $public_key = 'sample_public_key';

        $json = json_encode(array(
            $kid => $public_key
        ));

        $public_keys = new PublicKeys($json);

        $this->assertNull($public_keys->getPublicKey('invalid_kid'));
    }
}
