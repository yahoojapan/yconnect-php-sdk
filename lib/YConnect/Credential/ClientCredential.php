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
 * ClientCredentialクラス
 *
 * 認可サーバ、Tokenサーバのリクエストで用いられるクレデンシャルを保持するクラスです.
 */
class ClientCredential
{
    /**
     * @var string|null client_id
     */
    public $id = null;

    /**
     * @var string|null client_secret
     */
    public $secret = null;

    /**
     * ClientCredentialのインスタンス生成
     *
     * @param string $client_id クライアントID
     * @param string $client_secret クライアントシークレット
     */
    public function __construct($client_id, $client_secret)
    {
        $this->id = $client_id;
        $this->secret = $client_secret;
    }

    /**
     * toString
     *
     * @return string
     */
    public function __toString()
    {
        return "client_id: " . $this->id . ", client_secret: " . $this->secret;
    }

    /**
     * Authorization Header形式クレデンシャル取得メソッド
     *
     * @return string Authorization Header形式のクレデンシャル
     */
    public function toAuthorizationHeader()
    {
        return base64_encode($this->id . ":" . $this->secret);
    }

    /**
     * クエリ形式クレデンシャル取得メソッド
     *
     * @return string クエリ形式のクレデンシャル
     */
    public function toQueryString()
    {
        return http_build_query(
            array(
                "client_id"     => $this->id,
                "client_secret" => $this->secret
            )
        );
    }
}
