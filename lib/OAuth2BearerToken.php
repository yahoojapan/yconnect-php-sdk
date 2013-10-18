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

/** \file OAuth2BearerToken.php
 *
 * \brief Bearer Tokenを保持するクラスを定義しています.
 */

/**
 * \class OAuth2BearerTokenクラス
 *
 * \brief Bearer Tokenを保持するクラスです.
 *
 * APIアクセスで用いられるBearer Tokenのクラスです.
 */
class OAuth2BearerToken
{
    /**
     * \private \brief access_token
     */
    public $token = null;

    /**
     * \private \brief expiration
     */
    public $exp = null;

    /**
     * \brief OAuth2BearerTokenインスタンス生成
     *
     * @param	$access_token	Access Token
     * @param	$expiration	expiration
     */
    public function __construct( $access_token, $expiration  )
    {
        $this->token      = $access_token;
        $this->exp        = $expiration;
    }

    /**
     * \brief toString
     */
    function __toString()
    {
        return "Authorization: Bearer " . $this->token;
    }

    /**
     * \brief Authorization Header形式トークン取得メソッド
     */
    public function toAuthorizationHeader()
    {
        return $this->token;
    }

    /**
     * \brief クエリ形式トークン取得メソッド
     */
    public function toQueryString()
    {
        $query = http_build_query(
            array(
                "access_token" => $this->token
            )
        );
    
        return $query;
    }

    /**
     * \brief Access Token有効期限取得メソッド
     */
    public function getExpiration()
    {
        return $this->exp;
    }
}

/* vim:ts=4:sw=4:sts=0:tw=0:ft=php:set et: */
