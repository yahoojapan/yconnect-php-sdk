<?php
// TODO: 使われていないクラスのため削除予定
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

/** \file ClientCredentialsClient.php
 *
 * \brief Authorization Code フローの機能を実装しています.
 */

namespace YConnect\Credential;

use YConnect\Endpoint\TokenClient;

/**
 * \class ClientCredentialsClientクラス
 *
 * \brief Authorization Code フローの機能を実装したクラスです.
 */
class ClientCredentialsClient extends TokenClient
{
    /**
     * \private \brief scopes
     */
    private $scopes = null;

    /**
     * \private \brief access_token
     */
    private $access_token = null;

    /**
     * \brief ClientCredentialsClientのインスタンス生成
     */
    public function __construct($endpoint_url, $client_credential, $scopes)
    {
        parent::__construct( $endpoint_url, $client_credential );
        $this->scopes = $scopes;
    }

    /**
     * \brief scopes設定メソッド
     * @param	$scopes	scope
     */
    public function setScopes($scopes)
    {
        $this->scopes = $scopes;
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
     * \brief エンドポイントURL設定メソッド
     * @param	$endpoint_url	エンドポイントURL
     */
    protected function _setEndpointUrl($endpoint_url)
    {
        parent::_setEndpointUrl($endpoint_url);
    }
}
