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

/** \file TokenClient.php
 *
 * \brief OAuth2 Token処理クラスです.
 */

namespace YConnect\Endpoint;

use YConnect\Util\HttpClient;

/**
 * \class TokenClientクラス
 *
 * \brief Tokenリクエストの機能を実装したクラスです.
 */
class TokenClient
{
    /**
     * \private \brief エンドポイントURL
     */
    private $url = null;

    /**
     * \private \brief パラメータ
     */
    private $params = array();

    /**
     * \private \brief レスポンスボディ
     */
    private $res_body = null;

    /**
     * \private \brief クレデンシャルの文字列
     */
    protected $cred = null;

    /**
     * \brief TokenClientのインスタンス生成
     */
    public function __construct($endpoint_url, $client_credential)
    {
        $this->url  = $endpoint_url;
        $this->cred = $client_credential;
    }

    /**
     * \brief Tokenエンドポイントリソース取得メソッド
     */
    public function fetchToken()
    {
        $httpClient = new HttpClient();
        $httpClient->setHeader( array(
            "Expect:", // POST HTTP 100-continue 無効
            "Authorization: Basic " . $this->cred->toAuthorizationHeader()
        ));
        $httpClient->requestPost( $this->url, $this->params );
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
            return false;
        }
    }

    /**
     * \brief 複数パラメータ設定メソッド
     *
     * パラメータ名が重複している場合、後から追加された値を上書きします.
     *
     * @param	$keyval_array	パラメータ名と値の連想配列
     */
    public function setParams($keyval_array)
    {
        $this->params = array_merge( $this->params, $keyval_array );
    }

    /**
     * \brief パラメータ設定メソッド
     *
     * パラメータ名が重複している場合、後から追加された値を上書きします.
     *
     * @param	$key	パラメータ名
     * @param	$val	値
     */
    public function setParam($key, $val)
    {
        $this->params[ $key ] = $val;
    }

    /**
     * \brief エンドポイントURL設定メソッド
     * @param	$endpoint_url	エンドポイントURL
     */
    protected function _setEndpointUrl($endpoint_url)
    {
    }
}
