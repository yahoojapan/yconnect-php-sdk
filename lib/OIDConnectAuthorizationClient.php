<?php
/**
 * The MIT License (MIT)
 * 
 * Copyright (C) 2013 Yahoo Japan Corporation. All Rights Reserved. 
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

/** \file OIDConnectAuthorizationClient.php
 *
 * \brief OpenID Connect Authorization処理クラスです.
 */

/**
 * \class OIDConnectAuthorizationClientクラス
 *
 * \brief Authorizationの機能を実装したサブクラスです.
 */
class OIDConnectAuthorizationClient extends OAuth2AuthorizationClient
{
    /**
     * \private \brief
     */
    private $OIDConnectDisplay = OIDConnectDisplay::PAGE;

    /**
     * \private \brief 
     */
    private $OIDConnectPrompt = OIDConnectPrompt::LOGIN;

    /**
     * \brief OIDConnectAuthorizationClientのインスタンス生成
     */
    public function __construct( $endpoint_url, $client_credential, $response_type=null )
    {
        parent::__construct( $endpoint_url, $client_credential, $response_type );
    }

    /**
     * \brief 認可リクエストメソッド
     *
     * 認可サーバへAuthorozation Codeをリクエストします.
     *
     * @param	$redirect_uri	リダイレクトURI
     * @param	$state	state
     */
    public function requestAuthorizationGrant( $redirect_uri=null, $state=null )
    {
        parent::setParam( "display", $this->OIDConnectDisplay );
        parent::setParam( "prompt", $this->OIDConnectPrompt );

        parent::requestAuthorizationGrant( $redirect_uri, $state );
    }

    /**
     * \brief display設定メソッド
     * @param	$display	display
     */
    public function setDisplay( $display )
    {
        $this->OIDConnectDisplay = $display;
    } 

    /**
     * \brief prompt設定メソッド
     * @param	$prompt	prompt
     */
    public function setPrompt( $prompt )
    {
        $this->OIDConnectPrompt = $prompt;
    }
}


/* vim:ts=4:sw=4:sts=0:tw=0:ft=php:set et: */
