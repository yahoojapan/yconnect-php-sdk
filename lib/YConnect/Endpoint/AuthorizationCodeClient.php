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

/** \file AuthorizationCodeClient.php
 *
 * \brief Authorization Code フローの機能を実装しています.
 */

namespace YConnect\Endpoint;

use YConnect\Endpoint\TokenClient;
use YConnect\Constant\GrantType;
use YConnect\Util\Logger;
use YConnect\Credential\BearerToken;
use YConnect\Credential\RefreshToken;
use YConnect\Credential\IdToken;
use YConnect\Exception\TokenException;

/**
 * \class AuthorizationCodeClientクラス
 *
 * \brief Authorization Code フローの機能を実装したクラスです.
 */
class AuthorizationCodeClient extends TokenClient
{
    /**
     * \private \brief 認可コード
     */
    private $code = null;

    /**
     * \private \brief リダイレクトURI
     */
    private $redirect_uri = null;

    /**
     * \private \brief Access Token
     */
    private $access_token = null;

    /**
     * \private \brief Refresh Token
     */
    private $refresh_token = null;

    /**
     * \private \brief ID Token
     */
    private $id_token = null;

    /**
     * \brief AuthorizationCodeClientのインスタンス生成
     */
    public function __construct($endpoint_url, $client_credential, $code, $redirect_uri)
    {
        parent::__construct( $endpoint_url, $client_credential );
        $this->code = $code;
        $this->redirect_uri = $redirect_uri;
    }

    /**
     * \brief code設定メソッド
     * @param	$code	認可コード
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * \brief redirect_uri設定メソッド
     * @param	$redirect_uri	リダイレクトURL
     */
    public function setRedirectUri($redirect_uri)
    {
        $this->redirect_uri = $redirect_uri;
    }

    /**
     * \brief Access Token取得メソッド
     * @return	access_token
     */
    public function getAccessToken()
    {
        if( $this->access_token != null ) {
            return $this->access_token;
        } else {
            return false;
        }
    }

    /**
     * \brief Refresh Token取得メソッド
     * @return	refresh_token
     */
    public function getRefreshToken()
    {
        if( $this->refresh_token != null ) {
            return $this->refresh_token;
        } else {
            return false;
        }
    }

    /**
     * \brief ID Token取得メソッド
     * @return	id_token
     */
    public function getIdToken()
    {
        if( $this->id_token != null ) {
            return $this->id_token;
        } else {
            return false;
        }
    }

    /**
     * \brief Tokenエンドポイントリソース取得メソッド
     */
    public function fetchToken()
    {
        parent::setParam( "grant_type", GrantType::AUTHORIZATION_CODE );
        parent::setParam( "code", $this->code );
        parent::setParam( "redirect_uri", $this->redirect_uri );

        parent::fetchToken();

        $res_body = parent::getResponse();

        // JSONパラメータ抽出処理
        $json_response = json_decode( $res_body, true );
        Logger::debug( "json response(" . get_class() . "::" . __FUNCTION__ . ")", $json_response );
        if( $json_response != null ) {
            if( empty( $json_response["error"] ) ) {
                $access_token  = $json_response["access_token"];
                $exp           = $json_response["expires_in"];
                $refresh_token = $json_response["refresh_token"];
                $this->access_token  = new BearerToken( $access_token, $exp );
                $this->refresh_token = new RefreshToken( $refresh_token );
                if(array_key_exists("id_token", $json_response)) {
                    $id_token = $json_response["id_token"];
                    $id_token_object = new IdToken( $id_token, $this->cred->secret );
                    $this->id_token = $id_token_object->getIdToken();
                }
            } else {
                $error      = $json_response["error"];
                $error_desc = $json_response["error_description"];
                Logger::error( $error . "(" . get_class() . "::" . __FUNCTION__ . ")", $error_desc );
                throw new TokenException( $error, $error_desc );
            }
        } else {
            Logger::error( "no_response(" . get_class() . "::" . __FUNCTION__ . ")", "Failed to get the response body" );
            throw new TokenException( "no_response", "Failed to get the response body" );
        }

        Logger::debug( "token endpoint response(" . get_class() . "::" . __FUNCTION__ . ")",
            array(
                $this->access_token,
                $this->refresh_token
            )
        );
        Logger::info( "got access and refresh token(" . get_class() . "::" . __FUNCTION__ . ")" );
    }

    /**
     * \brief エンドポイントURL設定メソッド
     * @param	$endpoint_url	エンドポイントURL
     */
    protected function _setEndpointUrl($endpoint_url)
    {
        $this->url = $endpoint_url;
    }
}
