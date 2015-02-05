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

/** \file AuthorizationClient.php
 *
 * \brief OAuth2 Authorization処理クラスです.
 */

namespace YConnect\Endpoint;

use YConnect\Constant\ResponseType;
use YConnect\Util\Logger;

/**
 * \class AuthorizationClientクラス
 *
 * \brief Authorizationの機能を実装したクラスです.
 */
class AuthorizationClient
{
    /**
     * \private \brief 認可サーバエンドポイントURL
     */
    private $url = null;

    /**
     * \private \brief クレデンシャル
     */
    private $cred = null;

    /**
     * \private \brief レスポンスタイプ
     */
    private $response_type = ResponseType::CODE;

    /**
     * \private \brief パラメータ
     */
    private $params = array();

    /**
     * \brief AuthorizationClientのインスタンス生成
     *
     * @param	$endpoint_url	エンドポイントURL
     * @param	$client_credential	クライアントクレデンシャル
     * @param	$response_type	response_type
     */
    public function __construct($endpoint_url, $client_credential, $response_type=null)
    {
        $this->url  = $endpoint_url;
        $this->cred = $client_credential;

        if( $response_type != null ) {
            $this->response_type = $response_type;
        }
    }

    /**
     * \brief 認可リクエストメソッド
     *
     * 認可サーバへAuthorozation Codeをリクエストします.
     *
     * @param	$redirect_uri	リダイレクトURI
     * @param	$state	state
     */
    public function requestAuthorizationGrant($redirect_uri=null, $state=null)
    {
        self::setParam( "response_type", $this->response_type );
        self::setParam( "client_id", $this->cred->id );

        // RECOMMENEDED
        if( $state != null ) {
            self::setParam( "state", $state );
        }

        // OPTIONAL
        if( $redirect_uri != null ) {
            $encoded_redirect_uri = urlencode( $redirect_uri );
            self::setParam( "redirect_uri", $redirect_uri );
        }

        $query = http_build_query( $this->params );
        $request_uri = $this->url . "?" .  $query;

        Logger::info( "authorization request(" . get_class() . "::" . __FUNCTION__ . ")", $request_uri );

        header( "Location: " . $request_uri );
        exit();
    }

    /**
     * \brief scope設定メソッド
     * @param	$scope_array	scope名の配列
     */
    public function setScopes($scope_array)
    {
        $this->params[ "scope" ] = implode( " ", $scope_array );
    }

    /**
     * \brief レスポンスタイプ設定メソッド
     * @param	$response_type	response_type
     */
    public function setResponseType($response_type)
    {
        $this->response_type = $response_type;
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
     * \brief 認可サーバエンドポイントURL設定メソッド
     * @param	$endpoint_url	エンドポイントURL
     */
    protected function _setEndpointUrl($endpoint_url)
    {
        $this->url = $endpoint_url;
    }

}
