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

namespace YConnect\WebAPI;

use YConnect\Endpoint\ApiClient;
use YConnect\Credential\BearerToken;
use YConnect\Exception\TokenException;
use YConnect\Util\Logger;
use YConnect\Exception\ApiException;

/**
 * UserInfoClientクラス
 *
 * UserInfoにアクセスする機能を実装したクラスです.
 */
class UserInfoClient extends ApiClient
{
    /**
     * @var string|null UserInfoエンドポイントURL
     */
    private $url;

    /**
     * @var object|null UserInfo配列
     */
    private $user_info = array();

    /**
     * UserInfoClientのインスタンス生成
     *
     * @param string $endpoint_url エンドポイントURL
     * @param string|BearerToken $access_token アクセストークン
     */
    public function __construct($endpoint_url, $access_token)
    {
        if (is_string($access_token)) {
            $access_token = new BearerToken($access_token, null);
        }

        parent::__construct($access_token);

        $this->url  = $endpoint_url;
    }

    /**
     * UserInfoエンドポイントリソース取得メソッド
     *
     * @throws TokenException レスポンスヘッダーにエラーが含まれているときに発生
     * @throws ApiException レスポンスボディにエラーが含まれているときに発生
     */
    public function fetchUserInfo()
    {
        parent::fetchResource($this->url, "GET");

        $res_body = parent::getLastResponse();

        $this->parseJson($res_body);
    }

    /**
     * UserInfo配列取得メソッド
     *
     * @return object|false UserInfoObject
     */
    public function getUserInfo()
    {
        if ($this->user_info != null) {
            return $this->user_info;
        } else {
            return false;
        }
    }

    /**
     * JSONパラメータ抽出処理
     *
     * @param string $json パースするJSON
     * @throws ApiException レスポンスにエラーが含まれているときに発生
     */
    private function parseJson($json)
    {
        $json_response = json_decode($json, true);
        Logger::debug("json response(" . get_class() . "::" . __FUNCTION__ . ")", $json_response);

        if (!$json_response) {
            Logger::error("no_response(" . get_class() . "::" . __FUNCTION__ . ")", "Failed to get the response body");
            throw new ApiException("no_response", "Failed to get the response body");
        }

        if (isset($json_response["error"])) {
            $error      = $json_response["error"];
            $error_desc = $json_response["error_description"];
            Logger::error($error . "(" . get_class() . "::" . __FUNCTION__ . ")", $error_desc);
            throw new ApiException($error, $error_desc);
        }

        $this->user_info = $json_response;
    }
}
