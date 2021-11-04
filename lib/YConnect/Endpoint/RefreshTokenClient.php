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
use YConnect\Constant\GrantType;
use YConnect\Credential\BearerToken;
use YConnect\Credential\ClientCredential;
use YConnect\Exception\TokenException;
use YConnect\Util\Logger;

/**
 * RefreshTokenClientクラス
 *
 * Refresh Token フローの機能を実装したクラスです.
 */
class RefreshTokenClient extends TokenClient
{
    /**
     * @var string リフレッシュトークン
     */
    private $refresh_token;

    /**
     * @var BearerToken|null アクセストークン
     */
    private $access_token = null;

    /**
     * RefreshTokenClientのインスタンス生成
     *
     * @param string $endpoint_uri エンドポイントURI
     * @param ClientCredential $client_credential 認証情報
     * @param string $refresh_token リフレッシュトークン
     */
    public function __construct($endpoint_uri, $client_credential, $refresh_token)
    {
        parent::__construct($endpoint_uri, $client_credential);
        $this->refresh_token = $refresh_token;
    }

    /**
     * Refresh Token設定メソッド
     *
     * @param string $refresh_token リフレッシュトークン
     */
    public function setRefreshToken($refresh_token)
    {
        $this->refresh_token = $refresh_token;
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
     * Tokenエンドポイントリソース取得メソッド
     *
     * @throws TokenException レスポンスにエラーを含むときに発生
     * @throws Exception HTTPリクエストに失敗したときに発生
     */
    public function fetchToken()
    {
        parent::setParam("grant_type", GrantType::REFRESH_TOKEN);
        parent::setParam("refresh_token", $this->refresh_token);

        parent::fetchToken();

        $res_body = parent::getResponse();

        $this->parseJson($res_body);

        Logger::debug(
            "refresh token response(" . get_class() . "::" . __FUNCTION__ . ")",
            array(
                $this->access_token,
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
     * @param string $json パースするJSON
     * @throws TokenException レスポンスにエラーを含むときに発生
     */
    private function parseJson($json)
    {
        $json_response = json_decode($json, true);
        Logger::debug("json response(" . get_class() . "::" . __FUNCTION__ . ")", $json_response);

        $this->checkErrorResponse($json_response);

        $access_token = $json_response["access_token"];
        $exp = $json_response["expires_in"];
        $this->access_token = new BearerToken($access_token, $exp);
    }
}
