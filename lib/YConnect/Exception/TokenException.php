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

/** \file TokenException.php
 *
 * \brief Token サーバ例外処理クラスを定義しています.
 */

namespace YConnect\Exception;

/**
 * \class TokenExceptionクラス
 *
 * \brief Token サーバ例外処理クラスです.
 *
 * Token サーバ例外処理クラスです.
 */
class TokenException extends \Exception
{
    /**
     * \brief エラー詳細
     */
    public $error_detail = null;

    /**
     * \brief インスタンス生成
     *
     * @param	$error	エラー概要
     * @param	$error_desc	エラー詳細
     * @param	$code
     */
    public function __construct($error, $error_detail = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct($error, $code, $previous);
        $this->error_detail = $error_detail;
    }

    /**
     * \brief リダイレクトURIエラー確認メソッド
     *
     * @return	true or false
     */
    public function invalidRedirectUri()
    {
        if( preg_match( "/invalid_redirect_uri/", $this->message ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * \brief クライアント認証URIエラー確認メソッド
     *
     * @return	true or false
     */
    public function invalidClient()
    {
        if( preg_match( "/invalid_client/", $this->message ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * \brief scopeエラー確認メソッド
     *
     * @return	true or false
     */
    public function invalidScope()
    {
        if( preg_match( "/invalid_scope/", $this->message ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * \brief Refresh Token有効期限切れ確認メソッド
     *
     * @return	true or false
     */
    public function invalidGrant()
    {
        if( preg_match( "/invalid_grant/", $this->message ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * \brief Access Token有効期限切れ確認メソッド
     *
     * @return	true or false
     */
    public function tokenExpired()
    {
        if( preg_match( "/invalid_token/", $this->message ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * \brief 無効なToken確認メソッド
     *
     * @return	true or false
     */
    public function invalidToken()
    {
        if( preg_match( "/invalid_token/", $this->message ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * \brief パラメータ関連エラー確認メソッド
     *
     * @return	true or false
     */
    public function invalidRequest()
    {
        if( preg_match( "/invalid_request/", $this->message ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * \brief 認可タイプエラー確認メソッド
     *
     * @return	true or false
     */
    public function unsupportedGrantType()
    {
        if( preg_match( "/unsupported_grant_type/", $this->message ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * \brief HTTPSエラー確認メソッド
     *
     * @return	true or false
     */
    public function accessDenied()
    {
        if( preg_match( "/access_denied/", $this->message ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * \brief サーバーエラー確認メソッド
     *
     * @return	true or false
     */
    public function serverError()
    {
        if( preg_match( "/server_error/", $this->message ) ) {
            return true;
        } else {
            return false;
        }
    }

    public function __toString()
    {
        $str = __CLASS__ . ": " . $this->message . " ( $this->error_detail )";
        return $str;
    }
}
