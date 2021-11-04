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

namespace YConnect;

use Exception;
use UnexpectedValueException;
use YConnect\Constant\GrantType;
use YConnect\Credential\BearerToken;
use YConnect\Credential\ClientCredential;
use YConnect\Credential\IdToken;
use YConnect\Credential\PublicKeys;
use YConnect\Credential\RefreshToken;
use YConnect\Endpoint\AuthorizationClient;
use YConnect\Endpoint\AuthorizationCodeClient;
use YConnect\Endpoint\PublicKeysClient;
use YConnect\Endpoint\RefreshTokenClient;
use YConnect\Exception\ApiException;
use YConnect\Exception\IdTokenException;
use YConnect\Exception\TokenException;
use YConnect\Util\HttpClient;
use YConnect\Util\Logger;
use YConnect\WebAPI\UserInfoClient;

/**
 * YConnectClientクラス
 *
 * Yahoo! JAPAN Connect クライアントライブラリ
 */
class YConnectClient
{
    /**
     * Authorization Endpoint
     */
    const AUTHORIZATION_URL = "https://auth.login.yahoo.co.jp/yconnect/v2/authorization";

    /**
     * Token Endpoint
     */
    const TOKEN_URL = "https://auth.login.yahoo.co.jp/yconnect/v2/token";

    /**
     * UserInfo Endpoint
     */
    const USERINFO_URL = "https://userinfo.yahooapis.jp/yconnect/v2/attribute";

    /**
     * PublicKeys Endpoint
     */
    const PUBLIC_KEYS_ENDPOINT_URL = "https://auth.login.yahoo.co.jp/yconnect/v2/public-keys";

    /**
     * @var ClientCredential ClientCredentialインスタンス
     */
    private $clientCred;

    /**
     * @var BearerToken|null アクセストークン
     */
    private $access_token = null;

    /**
     * @var RefreshToken|null リフレッシュトークン
     */
    private $refresh_token = null;

    /**
     * @var int|null アクセストークンの有効期限
     */
    private $expiration = null;

    /**
     * @var object|null Idトークン
     */
    private $id_token = null;

    /**
     * @var object|null UserInfo
     */
    private $user_info = null;

    /**
     * インスタンス生成
     *
     * @param ClientCredential $clientCred 認証情報
     */
    public function __construct($clientCred)
    {
        $this->clientCred = $clientCred;
    }

    /**
     * デバッグ用出力メソッド
     *
     * @param bool $display true:コンソール出力 false:ログファイル出力
     */
    public function enableDebugMode($display = false)
    {
        if ($display) {
            Logger::setLogType(Logger::CONSOLE_TYPE);
        }
        Logger::setLogLevel(Logger::DEBUG);
    }

    /**
     * SSL証明書チェック解除メソッド
     */
    public function disableSSLCheck()
    {
        HttpClient::disableSSLCheck();
    }

    /**
     * 認可リクエストメソッド
     *
     * Authorizationエンドポイントにリクエストして同意画面を表示する。
     *
     * @param string $redirect_uri クライアントリダイレクトURL
     * @param string $state state(リクエストとコールバック間の検証用ランダム値)
     * @param string $nonce nonce(リプレイアタック対策のランダム値)
     * @param string $response_type response_type
     * @param string[]|null $scope
     * @param string|null $display display(認証画面タイプ)
     * @param string[]|null $prompt prompt(ログイン、同意画面選択)
     * @param int|null $max_age max_age(最大認証経過時間)
     * @param string|null $plain_code_challenge code_challenge(認可コード横取り攻撃対策（PKCE）のパラメーター)
     */
    public function requestAuth(
        $redirect_uri,
        $state,
        $nonce,
        $response_type,
        $scope = null,
        $display = null,
        $prompt = null,
        $max_age = null,
        $plain_code_challenge = null
    ) {
        $auth_client = $this->getAuthorizationClient(self::AUTHORIZATION_URL, $this->clientCred, $response_type);
        $auth_client->setParam("nonce", $nonce);
        if ($scope != null) {
            $auth_client->setParam("scope", implode(" ", $scope));
        }
        if ($display != null) {
            $auth_client->setParam("display", $display);
        }
        if ($prompt != null) {
            $auth_client->setParam("prompt", implode(" ", $prompt));
        }
        if ($max_age != null) {
            $auth_client->setParam("max_age", $max_age);
        }
        if ($plain_code_challenge != null) {
            $auth_client->setParam("code_challenge", $this->generateCodeChallenge($plain_code_challenge));
            $auth_client->setParam("code_challenge_method", "S256");
        }
        $auth_client->requestAuthorizationGrant($redirect_uri, $state);
    }

    /**
     * サポートしているレスポンス確認メソッド
     *
     * @param string $state state
     * @throws TokenException stateが一致しないときに発生
     */
    private function checkResponse($state)
    {
        if (!isset($_GET["state"])) {
            return false;
        }

        if ($state != $_GET["state"]) {
            throw new TokenException("not_matched_state", "the state did not match");
        }

        return true;
    }

    /**
     * 認可コード取得メソッド
     *
     * コールバックURLからAuthorization Codeを抽出します。
     * stateを検証して正しければAuthorization Codeの値を、そうでなければfalseを返します。
     *
     * @param string $state state
     * @return string|false code
     * @throws TokenException パラメータにエラーが含まれているときに発生
     */
    public function getAuthorizationCode($state)
    {
        if (self::checkResponse($state)) {
            $error      = array_key_exists("error", $_GET) ? $_GET["error"] : null;
            $error_desc = array_key_exists("error_description", $_GET) ? $_GET["error_description"] : null;
            $error_code = array_key_exists("error_code", $_GET) ? $_GET["error_code"] : null;

            if (!empty($error)) {
                throw new TokenException($error, $error_desc, $error_code);
            }

            if (!isset($_GET["code"])) {
                return false;
            }

            return $_GET["code"];
        } else {
            return false;
        }
    }

    /**
     * アクセストークンリクエストメソッド
     *
     * Tokenエンドポイントにリクエストします。
     *
     * @param string $redirect_uri クライアントリダイレクトURL
     * @param string $code code
     * @param string|null $code_verifier code verifier
     * @throws UnexpectedValueException 公開鍵リストが取得できなかったときに発生
     * @throws TokenException レスポンスにエラーが含まれているときに発生
     * @throws Exception curlコマンドの実行に失敗したときに発生
     */
    public function requestAccessToken($redirect_uri, $code, $code_verifier = null)
    {
        $public_keys_client = $this->getPublicKeysClient(self::PUBLIC_KEYS_ENDPOINT_URL);
        $public_keys_client->fetchPublicKeys();
        $public_keys_json = $public_keys_client->getResponse();
        if (!$public_keys_json) {
            throw new UnexpectedValueException('Failed to fetch public keys');
        }

        $auth_code_client = $this->getAuthorizationCodeClient(
            self::TOKEN_URL,
            $this->clientCred,
            $code,
            $redirect_uri,
            $public_keys_json
        );
        $token_req_params = array(
            "grant_type" => GrantType::AUTHORIZATION_CODE,
            "code"       => $code
        );
        $auth_code_client->setParams($token_req_params);

        if ($code_verifier != null) {
            $auth_code_client->setParam("code_verifier", $code_verifier);
        }

        $auth_code_client->fetchToken();
        $this->access_token  = $auth_code_client->getAccessToken();
        $this->refresh_token = $auth_code_client->getRefreshToken();
        $this->expiration    = $this->access_token->getExpiration();
        $this->id_token      = $auth_code_client->getIdToken();
    }

    /**
     * アクセストークン取得メソッド
     *
     * @return string アクセストークン
     */
    public function getAccessToken()
    {
        return $this->access_token->toAuthorizationHeader();
    }

    /**
     * リフレッシュトークン取得メソッド
     *
     * @return string リフレッシュトークン
     */
    public function getRefreshToken()
    {
        return $this->refresh_token->toAuthorizationHeader();
    }

    /**
     * アクセストークン有効期限取得メソッド
     *
     * @return int expiration
     */
    public function getAccessTokenExpiration()
    {
        return $this->expiration;
    }

    /**
     * IDトークン検証メソッド
     *
     * IDトークンの各パラメータの値を検証します。
     *
     * @return bool 検証に成功したときtrue
     * @throws IdTokenException 検証に失敗したときに発生
     */
    public function verifyIdToken($nonce, $access_token)
    {
        return IdToken::verify($this->id_token, $nonce, $this->clientCred->id, $access_token);
    }

    /**
     * IDトークン取得メソッド
     *
     * @return object IDトークン
     */
    public function getIdToken()
    {
        return $this->id_token;
    }

    /**
     * アクセストークン更新メソッド
     *
     * Tokenエンドポイントにリクエストします。
     * リフレッシュトークンをつかってアクセストークンを更新します。
     *
     * @param string $refresh_token リフレッシュトークン
     * @throws TokenException レスポンスにエラーが含まれているときに発生
     */
    public function refreshAccessToken($refresh_token)
    {
        $refresh_token_client = $this->getRefreshTokenClient(self::TOKEN_URL, $this->clientCred, $refresh_token);
        $refresh_token_client->fetchToken();
        $this->access_token  = $refresh_token_client->getAccessToken();
        $this->expiration    = $this->access_token->getExpiration();
    }

    /**
     * \brief UserInfoリクエストメソッド
     *
     * UserInfoエンドポイントにリクエストします。
     *
     * @param string|BearerToken $access_token アクセストークン
     * @throws ApiException レスポンスボディにエラーが含まれているときに発生
     * @throws TokenException レスポンスヘッダーにエラーが含まれているときに発生
     */
    public function requestUserInfo($access_token)
    {
        $user_info_client = $this->getUserInfoClient(self::USERINFO_URL, $access_token);
        $user_info_client->fetchUserInfo();
        $this->user_info = $user_info_client->getUserInfo();
    }

    /**
     * UserInfo取得メソッド
     *
     * ユーザー識別子などをstdClassのインスタンスとして取得します。
     *
     * @return object UserInfo
     */
    public function getUserInfo()
    {
        return $this->user_info;
    }

    /**
     * ハッシュ化された code challenge を生成
     *
     * @param string $plain_code_challenge ハッシュ化前の code challenge
     * @return string SHA-256でハッシュ化された code challenge
     */
    private function generateCodeChallenge($plain_code_challenge)
    {
        $hash = hash('sha256', $plain_code_challenge, true);
        return str_replace('=', '', strtr(base64_encode($hash), '+/', '-_'));
    }

    /**
     * authorization client取得
     *
     * @param string $endpoint エンドポイントURL
     * @param ClientCredential $client_cred 認証情報
     * @param string $response_type レスポンスタイプ
     * @return AuthorizationClient
     */
    protected function getAuthorizationClient($endpoint, $client_cred, $response_type)
    {
        return new AuthorizationClient(
            $endpoint,
            $client_cred,
            $response_type
        );
    }

    /**
     * public keys client取得
     *
     * @param string $endpoint エンドポイントURL
     * @return PublicKeysClient
     */
    protected function getPublicKeysClient($endpoint)
    {
        return new PublicKeysClient($endpoint);
    }

    /**
     * authorization code client 取得
     *
     * @param string $endpoint エンドポイントURL
     * @param ClientCredential $client_cred 認証情報
     * @param string $code code
     * @param string $redirect_uri リダイレクトURI
     * @param string $public_keys_json 公開鍵情報のJSON
     * @return AuthorizationCodeClient
     */
    protected function getAuthorizationCodeClient($endpoint, $client_cred, $code, $redirect_uri, $public_keys_json)
    {
        return new AuthorizationCodeClient(
            $endpoint,
            $client_cred,
            $code,
            $redirect_uri,
            new PublicKeys($public_keys_json)
        );
    }

    /**
     * refresh token client取得
     *
     * @param string $endpoint エンドポイントURL
     * @param ClientCredential $client_cred 認証情報
     * @param string $refresh_token リフレッシュトークン
     * @return RefreshTokenClient
     */
    protected function getRefreshTokenClient($endpoint, $client_cred, $refresh_token)
    {
        return new RefreshTokenClient(
            $endpoint,
            $client_cred,
            $refresh_token
        );
    }

    /**
     * user info client 取得
     *
     * @param string $endpoint エンドポイントURL
     * @param string|BearerToken $access_token アクセストークン
     * @return UserInfoClient
     */
    protected function getUserInfoClient($endpoint, $access_token)
    {
        return new UserInfoClient($endpoint, $access_token);
    }
}
