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

namespace YConnect\Endpoint;

use Exception;
use YConnect\Credential\ClientCredential;
use YConnect\Exception\TokenException;
use YConnect\Util\HttpClient;
use YConnect\Util\Logger;

/**
 * TokenClientクラス
 *
 * Tokenリクエストの機能を実装したクラスです.
 */
class TokenClient
{
    /**
     * @var string エンドポイントURL
     */
    private $url;

    /**
     * @var array<string, string|int> パラメータ
     */
    private $params = array();

    /**
     * @var string|null レスポンスボディ
     */
    private $res_body = null;

    /**
     * @var ClientCredential|null 認証情報
     */
    protected $cred = null;

    /**
     * TokenClientのインスタンス生成
     *
     * @param string $endpoint_url エンドポイントURL
     * @param ClientCredential $client_credential 認証情報
     */
    public function __construct($endpoint_url, $client_credential)
    {
        $this->url  = $endpoint_url;
        $this->cred = $client_credential;
    }

    /**
     * Tokenエンドポイントリソース取得メソッド
     *
     * @throws Exception HTTPリクエストに失敗したときに発生
     */
    public function fetchToken()
    {
        $httpClient = $this->getHttpClient();
        $httpClient->setHeader(array(
            "Expect:", // POST HTTP 100-continue 無効
            "Authorization: Basic " . $this->cred->toAuthorizationHeader()
        ));
        $httpClient->requestPost($this->url, $this->params);
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
            return false;
        }
    }

    /**
     * 複数パラメータ設定メソッド
     *
     * パラメータ名が重複している場合、後から追加された値を上書きします.
     *
     * @param array<string, string|int> $key_val_array パラメータ名と値の連想配列
     */
    public function setParams($key_val_array)
    {
        $this->params = array_merge($this->params, $key_val_array);
    }

    /**
     * パラメータ設定メソッド
     *
     * パラメータ名が重複している場合、後から追加された値を上書きします.
     *
     * @param string $key パラメータ名
     * @param string|int $val 値
     */
    public function setParam($key, $val)
    {
        $this->params[ $key ] = $val;
    }

    /**
     * エンドポイントURL設定メソッド
     *
     * @param string $endpoint_url エンドポイントURL
     */
    protected function setEndpointUrl($endpoint_url)
    {
        $this->url = $endpoint_url;
    }

    /**
     * レスポンスにエラーが含まれていないか確認
     *
     * @param array<string, string|int> $response 検査するレスポンス配列
     * @throws TokenException レスポンスにエラーが含まれているときに発生
     */
    protected function checkErrorResponse($response)
    {
        if (!$response) {
            Logger::error("no_response(" . get_class() . "::" . __FUNCTION__ . ")", "Failed to get the response body");
            throw new TokenException("no_response", "Failed to get the response body");
        }

        if (isset($response["error"])) {
            $error      = $response["error"];
            $error_desc = $response["error_description"];
            $error_code = $response["error_code"];
            Logger::error($error . "(" . get_class() . "::" . __FUNCTION__ . ")", $error_desc);
            throw new TokenException($error, $error_desc, $error_code);
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
