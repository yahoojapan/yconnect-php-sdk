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

/** \file ClientCredential.php
 *
 * \brief クレデンシャルを保持するクラスを定義しています.
 */

namespace YConnect\Credential;

/**
 * \class ClientCredentialクラス
 *
 * \brief クレデンシャルを保持するクラスです.
 *
 * 認可サーバ、Tokenサーバのリクエストで用いられるクレデンシャルのクラスです.
 */
class ClientCredential
{
    /**
     * \private \brief client_id
     */
    public $id = null;

    /**
     * \private \brief client_secret
     */
    public $secret = null;

    /**
     * \brief ClientCredentialのインスタンス生成
     *
     * @param	$client_id	Client ID
     * @param	$client_secret	Client Secret
     */
    public function __construct($client_id, $client_secret)
    {
        $this->id = $client_id;
        $this->secret = $client_secret;
    }

    /**
     * \brief toString
     */
    public function __toString()
    {
        return "client_id: " . $this->id . ", client_secret: " . $this->secret;
    }

    /**
     * \brief Authorization Header形式クレデンシャル取得メソッド
     */
    public function toAuthorizationHeader()
    {
        return base64_encode( $this->id . ":" . $this->secret );
    }

    /**
     * \brief クエリ形式取得メソッド
     */
    public function toQueryString()
    {
        $query = http_build_query(
            array(
                "client_id"     => $this->id,
                "client_secret" => $this->secret
            )
        );

        return $query;
    }
}
