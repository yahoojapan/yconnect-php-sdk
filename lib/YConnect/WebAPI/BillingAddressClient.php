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

/** \file BillingAddressClient.php
 *
 * \brief OAuth2 Authorization処理クラスです.
 */

namespace YConnect\WebAPI;

use YConnect\Endpoint\ApiClient;
use YConnect\Credential\BearerToken;
use YConnect\Util\Logger;
use YConnect\Exception\ApiException;

/**
 * \class BillingAddressClientクラス
 *
 * \brief Authorizationの機能を実装したクラスです.
 */
class BillingAddressClient extends ApiClient
{
    /**
     * \brief BillingAddress Endpoint
     */
    const BILLINGADDRESS_URL = "https://fastpay.yahooapis.jp/v1/address";

    /**
     * \private \brief BillingAddressエンドポイントURL
     */
    private $url = null;

    /**
     * \private \brief BillingAddress配列
     */
    private $billing_address = array();

    /**
     * \brief BillingAddressClientのインスタンス生成
     *
     * @param	$endpoint_url	エンドポイントURL
     */
    public function __construct( $access_token )
    {
        if( is_string($access_token) )
            $access_token = new BearerToken( $access_token, null );

        parent::__construct( $access_token );

        $this->url  = self::BILLINGADDRESS_URL;
    }

    /**
     * \brief BillingAddressエンドポイントリソース取得メソッド
     *
     */
    public function fetchBillingAddress()
    {
        parent::fetchResource( $this->url, "GET" );

        $res_body = parent::getLastResponse();

        $json_response = json_decode( $res_body, true );
        Logger::debug( "json response(" . get_class() . "::" . __FUNCTION__ . ")", $json_response );
        if( $json_response != null ) {
            if( empty( $json_response["error"] ) ) {
                $this->billing_address = $json_response;
            } else {
                $error      = $json_response["error"]["code"];
                $error_desc = $json_response["error"]["message"];
                Logger::error( $error . "(" . get_class() . "::" . __FUNCTION__ . ")", $error_desc );
                throw new ApiException( $error, $error_desc );
            }
        } else {
            Logger::error( "no_response(" . get_class() . "::" . __FUNCTION__ . ")", "Failed to get the response body" );
            throw new ApiException( "no_response", "Failed to get the response body" );
        }
    }

    /**
     * \brief BillingAddress配列取得メソッド
     *
     */
    public function getBillingAddress()
    {
        if( $this->billing_address != null ) {
            return $this->billing_address;
        } else {
            return array();
        }
    }
}
