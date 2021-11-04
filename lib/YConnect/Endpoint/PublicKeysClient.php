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
 * PublicKeysClientクラス
 *
 * Public keys APIアクセスに必要な機能を提供するクラスです.
 */
class PublicKeysClient
{
    /**
     * @var string Public keysサーバエンドポイントURL
     */
    private $url;

    /**
     * @var string|null レスポンスボディ
     */
    private $res_body = null;

    /**
     * PublicKeysClientのインスタンス生成
     *
     * @param string $endpoint_url エンドポイントURL
     */
    public function __construct($endpoint_url)
    {
        $this->url = $endpoint_url;
    }

    /**
     * APIエンドポイントリソース取得メソッド
     *
     * @throws Exception HTTPコールに失敗したときに発生
     */
    public function fetchPublicKeys()
    {
        $httpClient = $this->getHttpClient();
        $httpClient->requestGet($this->url);

        $this->res_body = $httpClient->getResponseBody();
    }

    /**
     * レスポンス取得メソッド
     *
     * @return string|null レスポンス
     */
    public function getResponse()
    {
        if ($this->res_body != null) {
            return $this->res_body;
        } else {
            return null;
        }
    }

    /**
     * HTTPクライアント取得メソッド
     *
     * @return HttpClient
     */
    protected function getHttpClient()
    {
        return new HttpClient();
    }
}
