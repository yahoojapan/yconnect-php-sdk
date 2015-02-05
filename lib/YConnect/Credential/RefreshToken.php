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

/** \file RefreshToken.php
 *
 * \brief Refresh Tokenを保持するクラスを定義しています.
 */

namespace YConnect\Credential;

/**
 * \class RefreshTokenクラス
 *
 * \brief Refresh Tokenを保持するクラスです.
 *
 * Access Tokenの更新で用いられるRefresh Tokenのクラスです.
 */
class RefreshToken
{
    /**
     * \private \brief refresh_token
     */
    public $token = null;

    /**
     * \brief RefreshTokenのインスタンス生成
     *
     * @param	$refresh_token	Refresh Token
     */
    public function __construct($refresh_token)
    {
        $this->token = $refresh_token;
    }

    /**
     * \brief toString
     */
    public function __toString()
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
                "refresh_token" => $this->token
            )
        );

        return $query;
    }
}
