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

/** \file ApiClient.php
 *
 * \brief Web APIアクセスの機能を提供するクラスを定義しています.
 */

namespace YConnect\Endpoint;

use YConnect\Util\HttpClient;
use YConnect\Exception\TokenException;
use YConnect\Credential\BearerToken;

/**
 * \class ApiClientクラス
 *
 * \brief Web APIアクセスの機能を提供するクラスです.
 *
 * Web APIアクセスに必要な機能を提供しています.
 */
class ApiClient
{
    /**
     * \private \brief Access Token
     */
    private $token;

    /**
     * \private \brief リクエストパラメータ
     */
    private $params = array();

    /**
     * \private \brief レスポンスボディ
     */
    private $res_body = null;

    /**
     * \private \brief レスポンスステータス
     */
    private $res_status = null;

    /**
     * \private \brief レスポンスエラーステータス
     */
    private $res_error = '';

    /**
     * \brief AuthorizationClientのインスタンス生成
     */
    public function __construct($access_token)
    {
        $this->_checkTokenType($access_token);
        $this->token = $access_token;
    }

    /**
     * \brief パラメータ設定メソッド
     *
     * パラメータ名が重複している場合、後から追加された値を上書きします.
     *
     * @param	$parameters パラメータ名と値の連想配列
     */
    protected function setParams($parameters = array())
    {
        if ( !is_array($parameters) )
            throw new \UnexpectedValueException('array is required');

        foreach ( $parameters as $key => $val )
            $this->setParam($key, $val);
    }

    /**
     * \brief 複数パラメータ設定メソッド
     *
     * パラメータ名が重複している場合、後から追加された値を上書きします.
     *
     * @param	$key	パラメータ名
     * @param   $val    値
     */
    protected function setParam($key, $val)
    {
        if ( !is_numeric($key) && is_string($key) && is_scalar($val) )
            $this->params[$key] = $val;
    }

    /**
     * \brief APIエンドポイントリソース取得メソッド
     * @param	$url	APIエンドポイント
     * @param	$method	HTTPリクエストメソッド
     * @throws  UnexpectedValueException
     */
    protected function fetchResource($url, $method)
    {
        $httpClient = new HttpClient();
        $httpClient->setHeader(array($this->token->__toString()));

        switch ( $method ) {
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
            throw new \UnexpectedValueException('unsupported http method');
        }


        $res_error_header = $httpClient->getResponseHeader('WWW-Authenticate');
        $this->_checkAuthorizationError($res_error_header);

        $this->res_body = $httpClient->getResponseBody();
    }

    /**
     * \brief レスポンス取得メソッド
     * @return	レスポンス
     */
    protected function getLastResponse()
    {
        return $this->res_body;
    }

    /**
     * check supported token type
     * Only Bearer Token is supported as of now
     *
     * @param   $token    Access Token
     * @throws  UnexpectedValueException
     */
    private function _checkTokenType($token)
    {
        if ( ! $token instanceof BearerToken )
            throw new \UnexpectedValueException('unsupported Access Token format');
    }

    /**
     * check WebAPI Authorication error
     *
     * @param   $header           WWW-Authenticate header string
     * @throws  TokenException    if WWW-Authenticate is not NULL
     * @see     TokenException
     */
    private function _checkAuthorizationError($header)
    {
        if ( $header !== NULL )
            throw new TokenException( $header );
    }
}
