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

class PublicKeys
{
    private $public_keys;

    /**
     * コンストラクタ
     *
     * @param string $json json文字列
     */
    public function __construct($json)
    {
        $this->public_keys = json_decode($json);
    }

    /**
     * kidから公開鍵を取得
     *
     * @param string $kid kid
     * @return string|null 公開鍵
     */
    public function getPublicKey($kid)
    {
        if (!isset($this->public_keys->$kid)) {
            return null;
        }
        return $this->public_keys->$kid;
    }
}
