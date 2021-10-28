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

namespace YConnect\Exception;

use Exception;

/**
 * TokenExceptionクラス
 *
 * Token サーバ例外処理クラスです.
 */
class TokenException extends Exception
{
    /**
     * @var string|null エラー詳細
     */
    public $error_detail = null;

    /**
     * @var int エラーコード
     */
    public $error_code;

    /**
     * インスタンス生成
     *
     * @param string $error エラー概要
     * @param string $error_detail エラー詳細
     * @param int $error_code エラーコード
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct($error, $error_detail = "", $error_code = 0, $code = 0, Exception $previous = null)
    {
        parent::__construct($error, $code, $previous);
        $this->error_detail = $error_detail;
        $this->error_code = $error_code;
    }

    /**
     * リダイレクトURIエラー確認メソッド
     *
     * @return bool リダイレクトURIエラーならtrue, そうでなければfalse
     */
    public function invalidRedirectUri()
    {
        if (preg_match("/invalid_redirect_uri/", $this->message)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * クライアント認証URIエラー確認メソッド
     *
     * @return bool クライアント認証URIエラーならtrue, そうでなければfalse
     */
    public function invalidClient()
    {
        if (preg_match("/invalid_client/", $this->message)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * スコープエラー確認メソッド
     *
     * @return bool スコープエラーならtrue, そうでなければfalse
     */
    public function invalidScope()
    {
        if (preg_match("/invalid_scope/", $this->message)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * リフレッシュトークン有効期限切れ確認メソッド
     *
     * @return bool リフレッシュトークン有効期限切れならtrue, そうでなければfalse
     */
    public function invalidGrant()
    {
        if (preg_match("/invalid_grant/", $this->message)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * アクセストークン有効期限切れ確認メソッド
     *
     * @return bool アクセストークン有効期限切れならtrue, そうでなければfalse
     */
    public function tokenExpired()
    {
        if (preg_match("/invalid_token/", $this->message)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 無効なトークン確認メソッド
     *
     * @return bool 無効なトークンならtrue, そうでなければfalse
     */
    public function invalidToken()
    {
        if (preg_match("/invalid_token/", $this->message)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * パラメータ関連エラー確認メソッド
     *
     * @return bool パラメータ関連エラーならtrue, そうでなければfalse
     */
    public function invalidRequest()
    {
        if (preg_match("/invalid_request/", $this->message)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 認可タイプエラー確認メソッド
     *
     * @return bool 認可タイプエラーならtrue, そうでなければfalse
     */
    public function unsupportedGrantType()
    {
        if (preg_match("/unsupported_grant_type/", $this->message)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * HTTPSエラー確認メソッド
     *
     * @return bool HTTPSエラーならtrue, そうでなければfalse
     */
    public function accessDenied()
    {
        if (preg_match("/access_denied/", $this->message)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * サーバーエラー確認メソッド
     *
     * @return bool サーバーエラーならtrue, そうでなければfalse
     */
    public function serverError()
    {
        if (preg_match("/server_error/", $this->message)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . ": " . $this->message . " ( $this->error_detail )[ $this->error_code ]";
    }
}
