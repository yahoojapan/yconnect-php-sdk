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
use UnexpectedValueException;
use YConnect\Util\HttpClient;
use YConnect\Exception\TokenException;
use YConnect\Credential\BearerToken;

/**
 * ApiClientクラス
 *
 * Web APIアクセスに必要な機能を提供するクラスです.
 */
class ApiClient
{
    /**
     * @var BearerToken アクセストークン
     */
    private $token;

    /**
     * @var array<string, string|int> リクエストパラメータ
     */
    private $params = array();

    /**
     * @var string レスポンスボディ
     */
    private $res_body = null;

    /**
     * ApiClientのインスタンス生成
     *
     * @param BearerToken $access_token アクセストークンオブジェクト
     */
    public function __construct($access_token)
    {
        $this->checkTokenType($access_token);
        $this->token = $access_token;
    }

    /**
     * パラメータ設定メソッド
     *
     * パラメータ名が重複している場合、後から追加された値を上書きします.
     *
     * @param array<string, string|int> $parameters パラメータ名と値の連想配列
     * @throws UnexpectedValueException parametersが配列ではないときに発生
     */
    protected function setParams($parameters = array())
    {
        if (!is_array($parameters)) {
            throw new UnexpectedValueException('array is required');
        }

        foreach ($parameters as $key => $val) {
            $this->setParam($key, $val);
        }
    }

    /**
     * 複数パラメータ設定メソッド
     *
     * パラメータ名が重複している場合、後から追加された値を上書きします.
     *
     * @param string $key パラメータ名
     * @param string|int $val 値
     */
    protected function setParam($key, $val)
    {
        if (!is_numeric($key) && is_string($key) && is_scalar($val)) {
            $this->params[$key] = $val;
        }
    }

    /**
     * APIエンドポイントリソース取得メソッド
     *
     * @param string $url APIエンドポイント
     * @param string $method HTTPリクエストメソッド
     * @throws UnexpectedValueException 対応していないHTTPリクエストメソッドが指定されたときに発生
     * @throws TokenException レスポンスヘッダーにエラーが含まれているときに発生
     * @throws Exception HTTPリクエストに失敗したときに発生
     */
    protected function fetchResource($url, $method)
    {
        $httpClient = $this->getHttpClient();
        $httpClient->setHeader(array(
            "Expect:", // POST HTTP 100-continue 無効
            (string)$this->token
        ));

        switch ($method) {
            case 'GET':
                $httpClient->requestGet($url, $this->params);
                break;
            case 'POST':
                $httpClient->requestPost($url, $this->params);
                // supported safe data RFC3986
                if (is_array($this->params)) {
                    foreach ($this->params as $key => $value) {
                        $this->params[$key] = rawurlencode(rawurldecode($value));
                    }
                }
                break;
            case 'PUT':
                $httpClient->requestPut($url, $this->params);
                break;
            case 'DELETE':
                $httpClient->requestDelete($url, $this->params);
                break;
            default:
                throw new UnexpectedValueException('unsupported http method');
        }


        $res_error_header = $httpClient->getResponseHeader('WWW-Authenticate');
        $this->checkAuthorizationError($res_error_header);

        $this->res_body = $httpClient->getResponseBody();
    }

    /**
     * レスポンスボディ取得メソッド
     *
     * @return string|null レスポンスボディ
     */
    protected function getLastResponse()
    {
        return $this->res_body;
    }

    /**
     * HTTPクライアント取得メソッド
     *
     * @return HttpClient HTTPクライアント
     */
    protected function getHttpClient()
    {
        return new HttpClient();
    }

    /**
     * トークンがサポートしているものか確認
     *
     * 現在はBearerトークンのみサポート
     *
     * @param BearerToken $token Accessトークン
     * @throws UnexpectedValueException 入力されたトークンがBearerTokenではないときに発生
     */
    private function checkTokenType($token)
    {
        if (!$token instanceof BearerToken) {
            throw new UnexpectedValueException('unsupported Access Token format');
        }
    }

    /**
     *  WebAPIで認証エラーが発生したか確認
     *
     * @param string|null $header WWW-Authenticateヘッダーの値
     * @throws TokenException WWW-Authenticateの値がnullではないときに発生
     */
    private function checkAuthorizationError($header)
    {
        if ($header !== null) {
            throw new TokenException($header);
        }
    }
}
