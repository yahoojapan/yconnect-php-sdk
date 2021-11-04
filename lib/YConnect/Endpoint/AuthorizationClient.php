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

use YConnect\Constant\ResponseType;
use YConnect\Credential\ClientCredential;
use YConnect\Util\Logger;

/**
 * AuthorizationClientクラス
 *
 * Authorizationの機能を実装したクラスです.
 */
class AuthorizationClient
{
    /**
     * @var string 認可サーバエンドポイントURL
     */
    private $url;

    /**
     * @var ClientCredential 認証情報
     */
    private $cred;

    /**
     * @var string レスポンスタイプ
     */
    private $response_type = ResponseType::CODE;

    /**
     * @var array<string, string|int> パラメータ
     */
    private $params = array();

    /**
     * AuthorizationClientのインスタンス生成
     *
     * @param string $endpoint_url エンドポイントURL
     * @param ClientCredential $client_credential クライアントクレデンシャル
     * @param string|null $response_type レスポンスタイプ
     */
    public function __construct($endpoint_url, $client_credential, $response_type = null)
    {
        $this->url  = $endpoint_url;
        $this->cred = $client_credential;

        if ($response_type != null) {
            $this->response_type = $response_type;
        }
    }

    /**
     * 認可リクエストメソッド
     *
     * 認可サーバへAuthorization Codeリクエストします.
     *
     * @param string $redirect_uri リダイレクトURI
     * @param string $state state
     */
    public function requestAuthorizationGrant($redirect_uri, $state = null)
    {
        self::setParam("response_type", $this->response_type);
        self::setParam("client_id", $this->cred->id);
        self::setParam("redirect_uri", $redirect_uri);

        // RECOMMENDED
        if ($state != null) {
            self::setParam("state", $state);
        }

        $query = http_build_query($this->params);
        $request_uri = $this->url . "?" .  $query;

        Logger::info("authorization request(" . get_class() . "::" . __FUNCTION__ . ")", $request_uri);

        $this->redirect($request_uri);
    }

    /**
     * scope設定メソッド
     *
     * @param string[] $scope_array scope名の配列
     */
    public function setScopes($scope_array)
    {
        $this->params[ "scope" ] = implode(" ", $scope_array);
    }

    /**
     * レスポンスタイプ設定メソッド
     *
     * @param string $response_type レスポンスタイプ
     */
    public function setResponseType($response_type)
    {
        $this->response_type = $response_type;
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
     * 認可サーバエンドポイントURL設定メソッド
     *
     * @param string $endpoint_url エンドポイントURL
     */
    protected function setEndpointUrl($endpoint_url)
    {
        $this->url = $endpoint_url;
    }

    /**
     * リダイレクト実行
     *
     * @param string $request_uri リダイレクト先のURL
     */
    protected function redirect($request_uri)
    {
        header("Location: " . $request_uri);
        exit();
    }
}
