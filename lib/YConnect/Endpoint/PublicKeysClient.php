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

namespace YConnect\Endpoint;


use Exception;
use YConnect\Util\HttpClient;

/**
 * \class PublicKeysClientクラス
 *
 * \brief Public keys APIアクセスの機能を提供するクラスです.
 *
 * Public keys APIアクセスに必要な機能を提供しています.
 */
class PublicKeysClient
{
    /**
     * \private \brief Public keysサーバエンドポイントURL
     */
    private $url;

    /**
     * \private \brief レスポンスボディ
     */
    private $res_body = null;

    /**
     * \private \brief レスポンスステータス
     */
    private $res_status = null;

    /**
     * \brief PublicKeysClientのインスタンス生成
     *
     * @param string $endpoint_url エンドポイントURL
     */
    public function __construct($endpoint_url)
    {
        $this->url  = $endpoint_url;
    }

    /**
     * \brief APIエンドポイントリソース取得メソッド
     *
     * @throws Exception
     */
    public function fetchPublicKeys()
    {
        $httpClient = $this->_getHttpClient();
        $httpClient->requestGet($this->url);

        $this->res_body = $httpClient->getResponseBody();
    }

    /**
     * \brief レスポンス取得メソッド
     * @return	レスポンス
     */
    public function getResponse()
    {
        if( $this->res_body != null ) {
            return $this->res_body;
        } else {
            return null;
        }
    }

    protected function _getHttpClient() {
        return new HttpClient();
    }
}
