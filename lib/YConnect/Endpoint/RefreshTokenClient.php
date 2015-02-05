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

use YConnect\Endpoint\TokenClient;
use YConnect\Constant\GrantType;
use YConnect\Util\Logger;
use YConnect\Credential\BearerToken;
use YConnect\Exception\TokenException;

/** \file RefreshTokenClient.php
 *
 * \brief Refresh Token フローの機能を実装しています.
 */

/**
 * \class RefreshTokenClientクラス
 *
 * \brief Refresh Token フローの機能を実装したクラスです.
 */
class RefreshTokenClient extends TokenClient
{
    /**
     * \private \brief Refresh Token
     */
    private $refresh_token = null;

    /**
     * \private \brief Access Token
     */
    private $access_token = null;

    /**
     * \brief RefreshTokenClientのインスタンス生成
     */
    public function __construct($endpoint_uri, $client_credential, $refresh_token)
    {
        parent::__construct( $endpoint_uri, $client_credential );
        $this->refresh_token = $refresh_token;
    }

    /**
     * \brief Refresh Token設定メソッド
     * @param	$refresh_token	Refresh Token
     */
    public function setRefreshToken($refresh_token)
    {
        $this->refresh_token = $refresh_token;
    }

    /**
     * \brief Access Token取得メソッド
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
     * \brief Tokenエンドポイントリソース取得メソッド
     */
    public function fetchToken()
    {
        parent::setParam( "grant_type", GrantType::REFRESH_TOKEN );
        parent::setParam( "refresh_token", $this->refresh_token );

        parent::fetchToken();

        $res_body = parent::getResponse();

        // JSONパラメータ抽出処理
        $json_response = json_decode( $res_body, true );
        Logger::debug( "json response(" . get_class() . "::" . __FUNCTION__ . ")", $json_response );
        if( $json_response != null ) {
            if( empty( $json_response["error"] ) ) {
                $access_token  = $json_response["access_token"];
                $exp           = $json_response["expires_in"];
                $this->access_token = new BearerToken( $access_token, $exp );
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

        Logger::debug( "refresh token response(" . get_class() . "::" . __FUNCTION__ . ")",
            array(
                $this->access_token,
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
