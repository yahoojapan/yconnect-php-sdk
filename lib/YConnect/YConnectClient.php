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

/**
 * \file YConnectClient.php
 *
 * \brief Yahoo! JAPAN Connect クライアントライブラリ
 */

namespace YConnect;

use YConnect\Constant\GrantType;
use YConnect\Credential\IdToken;
use YConnect\Credential\PublicKeys;
use YConnect\Endpoint\AuthorizationClient;
use YConnect\Endpoint\AuthorizationCodeClient;
use YConnect\Endpoint\PublicKeysClient;
use YConnect\Endpoint\RefreshTokenClient;
use YConnect\Exception\TokenException;
use YConnect\Util\HttpClient;
use YConnect\Util\Logger;
use YConnect\WebAPI\UserInfoClient;

/**
 * \class YConnectClientクラス
 *
 * \brief Yahoo! JAPAN Connect クライアントライブラリ
 */
class YConnectClient
{
    /**
     * \brief Authorization Endpoint
     */
    const AUTHORIZATION_URL = "https://auth.login.yahoo.co.jp/yconnect/v2/authorization";

    /**
     * \brief Token Endpoint
     */
    const TOKEN_URL = "https://auth.login.yahoo.co.jp/yconnect/v2/token";

    /**
     * \brief UserInfo Endpoint
     */
    const USERINFO_URL = "https://userinfo.yahooapis.jp/yconnect/v2/attribute";

    /**
     * \brief PublicKeys Endpoint
     */
    const PUBLIC_KEYS_ENDPOINT_URL = "https://auth.login.yahoo.co.jp/yconnect/v2/public-keys";

    /**
     * \private \brief ClientCredentialインスタンス
     */
    private $clientCred = null;

    /**
     * \private \brief AuthorizationClientインスタンス
     */
    private $auth_client = null;

    /**
     * \private \brief AuthorizationCodeClientインスタンス
     */
    private $auth_code_client = null;

    /**
     * \private \brief RefreshTokenインスタンス
     */
    private $refresh_token_client = null;

    /**
     * \private \brief ClientCredentialsClientインスタンス
     */
    private $client_credentials_client = null;

    /**
     * \private \brief Access Token
     */
    private $access_token = null;

    /**
     * \private \brief Refresh Token
     */
    private $refresh_token = null;

    /**
     * \private \brief Access Token Expiration
     */
    private $expiration = null;

    /**
     * \private \brief IdToken
     */
    private $id_token = null;

    /**
     * \private \brief UserInfo
     */
    private $user_info = null;

    /**
     * \brief インスタンス生成
     *
     * @param	$clientCred	クライアントクレデンシャル
     */
    public function __construct($clientCred)
    {
        $this->clientCred = $clientCred;
    }

    /**
     * \brief デバッグ用出力メソッド
     *
     * @param $display	true:コンソール出力 false:ログファイル出力
     */
    public function enableDebugMode($display = false)
    {
        if( $display == true ) Logger::setLogType( Logger::CONSOLE_TYPE );
        Logger::setLogLevel( Logger::DEBUG );
    }

    /**
     * \brief SSL証明書チェック解除メソッド
     *
     */
    public function disableSSLCheck()
    {
        HttpClient::disableSSLCheck();
    }

    /**
     * \brief 認可リクエストメソッド
     *
     * Authorizationエンドポイントにリクエストして同意画面を表示する。
     *
     * @param	$redirect_uri	クライアントリダイレクトURL
     * @param	$state	state(リクエストとコールバック間の検証用ランダム値)
     * @param	$nonce	nonce(リプレイアタック対策のランダム値)
     * @param	$response_type   response_type
     * @param	$display	display(認証画面タイプ)
     * @param   $prompt	prompt(ログイン、同意画面選択)
     * @param   $max_age max_age(最大認証経過時間)
     * @param   $code_challenge code_challenge(認可コード横取り攻撃対策（PKCE）のパラメーター)
     */
    public function requestAuth($redirect_uri, $state, $nonce, $response_type, $scope = null, $display = null,
                                $prompt = null, $max_age = null, $plain_code_challenge = null)
    {
        $auth_client = new AuthorizationClient(
            self::AUTHORIZATION_URL,
            $this->clientCred,
            $response_type
        );
        $auth_client->setParam( "nonce", $nonce );
        if( $scope != null ) {
            $auth_client->setParam( "scope", implode( " ", $scope ) );
        }
        if( $display != null ) $auth_client->setParam( "display", $display );
        if( $prompt != null ) {
            $auth_client->setParam( "prompt", implode( " ", $prompt ) );
        }
        if( $max_age != null ) {
            $auth_client->setParam( "max_age", $max_age);
        }
        if( $plain_code_challenge != null ) {
            $auth_client->setParam( "code_challenge", $this->_generateCodeChallenge($plain_code_challenge) );
            $auth_client->setParam( "code_challenge_method", "S256" );
        }
        $auth_client->requestAuthorizationGrant( $redirect_uri, $state );
    }

    /**
     * \brief サポートしているレスポンス確認メソッド
     *
     * @param	$state	state
     * @param	$scope	scope
     * @throws  TokenException
     */
    private function _checkResponse($state, $scope = null)
    {
        if( !isset( $_GET["state"] ) ) return false;

        if( $state != $_GET["state"] )
            throw new TokenException( "not_matched_state", "the state did not match" );

        return true;
    }

    /**
     * \brief 認可コード取得メソッド
     *
     * コールバックURLからAuthorizaiton Codeを抽出します。
     * stateを検証して正しければAuthorizaiton Codeの値を、そうでなければfalseを返します。
     *
     * @param	$state	state
     */
    public function getAuthorizationCode($state)
    {
        if( self::_checkResponse( $state ) ) {

            $error      = array_key_exists( "error", $_GET ) ? $_GET["error"] : null;
            $error_desc = array_key_exists( "error_description", $_GET ) ? $_GET["error_description"] : null;
            $error_code = array_key_exists( "error_code", $_GET ) ? $_GET["error_code"] : null;
            if( !empty( $error ) ) {
                throw new TokenException( $error, $error_desc, $error_code );
            }

            if( !isset( $_GET["code"] ) ) return false;
            return $_GET["code"];
        } else {
            return false;
        }
    }

    /**
     * \brief アクセストークンリクエストメソッド
     *
     * Tokenエンドポイントにリクエストします。
     *
     * @param    $redirect_uri    クライアントリダイレクトURL
     * @param    $code code
     * @param    $code_verifier code verifier
     * @throws \Exception
     */
    public function requestAccessToken($redirect_uri, $code, $code_verifier = null)
    {
        $public_keys_client = new PublicKeysClient(self::PUBLIC_KEYS_ENDPOINT_URL);
        $public_keys_client->fetchPublicKeys();
        $public_keys_json = $public_keys_client->getResponse();
        if(!$public_keys_json) {
            throw new \UnexpectedValueException('Failed to fetch public keys');
        }

        $this->auth_code_client = new AuthorizationCodeClient(
            self::TOKEN_URL,
            $this->clientCred,
            $code,
            $redirect_uri,
            new PublicKeys($public_keys_json)
        );
        $token_req_params = array(
            "grant_type" => GrantType::AUTHORIZATION_CODE,
            "code"       => $code
        );
        $this->auth_code_client->setParams( $token_req_params );

        if( $code_verifier != null ) {
            $this->auth_code_client->setParam( "code_verifier", $code_verifier );
        }

        $this->auth_code_client->fetchToken();
        $this->access_token  = $this->auth_code_client->getAccessToken();
        $this->refresh_token = $this->auth_code_client->getRefreshToken();
        $this->expiration    = $this->access_token->getExpiration();
        $this->id_token      = $this->auth_code_client->getIdToken();
    }

    /**
     * \brief アクセストークン取得メソッド
     *
     * アクセストークンを取得します。
     *
     * @return	access_token
     */
    public function getAccessToken()
    {
        return $this->access_token->toAuthorizationHeader();
    }

    /**
     * \brief リフレッシュトークン取得メソッド
     *
     * リフレッシュトークンを取得します。
     *
     * @return	refresh_token
     */
    public function getRefreshToken()
    {
        return $this->refresh_token->toAuthorizationHeader();
    }

    /**
     * \brief アクセストークン有効期限取得メソッド
     *
     * アクセストークンの有効期限を取得します。
     *
     * @return	expiration
     */
    public function getAccessTokenExpiration()
    {
        return $this->expiration;
    }

    /**
     * \brief IDトークン検証メソッド
     *
     * IDトークンの各パラメータの値を検証します。
     *
     * @return boolean
     */
    public function verifyIdToken($nonce, $access_token)
    {
        return IdToken::verify( $this->id_token, $nonce, $this->clientCred->id, $access_token);
    }

    /**
     * \brief IDトークン取得メソッド
     *
     * IDトークンオブジェクトを取得します。
     *
     */
    public function getIdToken()
    {
        return $this->id_token;
    }

    /**
     * \brief アクセストークン更新メソッド
     *
     * Tokenエンドポイントにリクエストします。
     * リフレッシュトークンをつかってアクセストークンを更新します。
     *
     * @param	$refresh_token	リフレッシュトークン
     */
    public function refreshAccessToken($refresh_token)
    {
        $this->refresh_token_client = new RefreshTokenClient(
            self::TOKEN_URL,
            $this->clientCred,
            $refresh_token
        );
        $this->refresh_token_client->fetchToken();
        $this->access_token  = $this->refresh_token_client->getAccessToken();
        $this->expiration    = $this->access_token->getExpiration();
    }

    /**
     * \brief UserInfoリクエストメソッド
     *
     * UserInfoエンドポイントにリクエストします。
     *
     * @param	$access_token	アクセストークン
     */
    public function requestUserInfo($access_token)
    {
        $this->user_info_client = new UserInfoClient( self::USERINFO_URL, $access_token );
        $this->user_info_client->fetchUserInfo();
        $this->user_info = $this->user_info_client->getUserInfo();
    }

    /**
     * \brief UserInfo取得メソッド
     *
     * ユーザー識別子などをstdClassのインスタンスとして取得します。
     *
     */
    public function getUserInfo()
    {
        return $this->user_info;
    }

    /**
     * \brief ハッシュ化された code challenge を生成します
     *
     * @param string $plain_code_challenge ハッシュ化前の code challenge
     * @return string SHA-256でハッシュ化された code challenge
     */
    private function _generateCodeChallenge($plain_code_challenge)
    {
        $hash = hash('sha256', $plain_code_challenge, true);
        return str_replace('=', '', strtr(base64_encode($hash), '+/', '-_'));
    }
}
