<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (C) 2015 Yahoo Japan Corporation. All Rights Reserved.
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

/**
 * BearerTokenクラス
 *
 * APIアクセスで用いられるBearer Tokenを保持するクラスです.
 */
class BearerToken
{

    /**
     * @var string|null アクセストークン
     */
    public $token = null;

    /**
     * @var int|null アクセストークンの有効期間
     */
    public $exp = null;

    /**
     * BearerTokenインスタンス生成
     *
     * @param string $access_token アクセストークン
     * @param int $expiration アクセストークンの有効期間
     */
    public function __construct($access_token, $expiration)
    {
        $this->token      = $access_token;
        $this->exp        = $expiration;
    }

    /**
     * toString
     *
     * @return string
     */
    public function __toString()
    {
        return "Authorization: Bearer " . $this->token;
    }

    /**
     * Authorization Header形式トークン取得メソッド
     *
     * @return string Authorization Header形式のアクセストークン
     */
    public function toAuthorizationHeader()
    {
        return $this->token;
    }

    /**
     * クエリ形式トークン取得メソッド
     *
     * @return string クエリ形式のアクセストークン
     */
    public function toQueryString()
    {
        return http_build_query(
            array(
                "access_token" => $this->token
            )
        );
    }

    /**
     * アクセストークンの有効期限取得メソッド
     *
     * @return int アクセストークンの有効期限
     */
    public function getExpiration()
    {
        return $this->exp;
    }
}
