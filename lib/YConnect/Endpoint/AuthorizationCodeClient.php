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
use YConnect\Credential\PublicKeys;
use YConnect\Constant\GrantType;
use YConnect\Util\Logger;
use YConnect\Credential\BearerToken;
use YConnect\Credential\RefreshToken;
use YConnect\Credential\IdToken;
use YConnect\Exception\TokenException;

/**
 * AuthorizationCodeClientクラス
 *
 * Authorization Code フローの機能を実装したクラスです.
 */
class AuthorizationCodeClient extends TokenClient
{
    /**
     * @var string 認可コード
     */
    private $code;

    /**
     * @var string リダイレクトURI
     */
    private $redirect_uri;

    /**
     * @var BearerToken|null アクセストークン
     */
    private $access_token = null;

    /**
     * @var RefreshToken|null リフレッシュトークン
     */
    private $refresh_token = null;

    /**
     * @var PublicKeys 公開鍵リスト
     */
    private $public_keys;

    /**
     * @var object|null IDトークン
     */
    private $id_token = null;

    /**
     * AuthorizationCodeClientのインスタンス生成
     *
     * @param string $endpoint_url リクエストURL
     * @param ClientCredential $client_credential 認証情報
     * @param string $code 認可コード
     * @param string $redirect_uri リダイレクトURI
     * @param PublicKeys $public_keys 公開鍵リスト
     */
    public function __construct($endpoint_url, $client_credential, $code, $redirect_uri, $public_keys)
    {
        parent::__construct($endpoint_url, $client_credential);
        $this->code = $code;
        $this->redirect_uri = $redirect_uri;
        $this->public_keys = $public_keys;
    }

    /**
     * code設定メソッド
     *
     * @param string $code 認可コード
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * リダイレクトURI設定メソッド
     *
     * @param string $redirect_uri リダイレクトURL
     */
    public function setRedirectUri($redirect_uri)
    {
        $this->redirect_uri = $redirect_uri;
    }

    /**
     * アクセストークン取得メソッド
     *
     * @return BearerToken|false アクセストークン
     */
    public function getAccessToken()
    {
        if ($this->access_token != null) {
            return $this->access_token;
        } else {
            return false;
        }
    }

    /**
     * リフレッシュトークン取得メソッド
     *
     * @return RefreshToken|false リフレッシュトークン
     */
    public function getRefreshToken()
    {
        if ($this->refresh_token != null) {
            return $this->refresh_token;
        } else {
            return false;
        }
    }

    /**
     * IDトークン取得メソッド
     *
     * @return object|false idトークン
     */
    public function getIdToken()
    {
        if ($this->id_token != null) {
            return $this->id_token;
        } else {
            return false;
        }
    }

    /**
     * Tokenエンドポイントリソース取得メソッド
     *
     * @throws TokenException レスポンスにエラーが含まれているときに発生
     * @throws Exception HTTPリクエストに失敗したときに発生
     */
    public function fetchToken()
    {
        parent::setParam("grant_type", GrantType::AUTHORIZATION_CODE);
        parent::setParam("code", $this->code);
        parent::setParam("redirect_uri", $this->redirect_uri);

        parent::fetchToken();

        $res_body = parent::getResponse();

        $this->parseJson($res_body);

        Logger::debug(
            "token endpoint response(" . get_class() . "::" . __FUNCTION__ . ")",
            array(
                $this->access_token,
                $this->refresh_token
            )
        );
        Logger::info("got access and refresh token(" . get_class() . "::" . __FUNCTION__ . ")");
    }

    /**
     * エンドポイントURL設定メソッド
     *
     * @param string $endpoint_url エンドポイントURL
     */
    protected function setEndpointUrl($endpoint_url)
    {
        parent::setEndpointUrl($endpoint_url);
    }

    /**
     * JSONパラメータ抽出処理
     *
     * @param string $json パース対象のJSON
     * @throws TokenException レスポンスにエラーが含まれているときに発生
     */
    private function parseJson($json)
    {
        $json_response = json_decode($json, true);
        Logger::debug("json response(" . get_class() . "::" . __FUNCTION__ . ")", $json_response);

        $this->checkErrorResponse($json_response);

        $access_token = $json_response["access_token"];
        $exp = $json_response["expires_in"];
        $refresh_token = $json_response["refresh_token"];
        $this->access_token = new BearerToken($access_token, $exp);
        $this->refresh_token = new RefreshToken($refresh_token);
        if (array_key_exists("id_token", $json_response)) {
            $id_token = $json_response["id_token"];
            $id_token_object = $this->getIdTokenObject($id_token);
            $this->id_token = $id_token_object->getIdToken();
        }
    }

    /**
     * IDトークンオブジェクトを取得
     *
     * @param string $id_token IDトークンの文字列
     * @return IdToken
     */
    protected function getIdTokenObject($id_token)
    {
        return new IdToken($id_token, $this->public_keys);
    }
}
