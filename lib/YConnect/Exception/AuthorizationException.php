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
 * AuthorizationExceptionクラス
 *
 * 認可サーバ例外処理クラスです.
 */
class AuthorizationException extends Exception
{
    /**
     * @var string|null エラー詳細
     */
    public $error_detail = null;

    /**
     * インスタンス生成
     *
     * @param string $error エラー概要
     * @param string $error_detail エラー詳細
     * @param int $code エラーコード
     * @param Exception|null $previous
     */
    public function __construct($error, $error_detail = "", $code = 0, Exception $previous = null)
    {
        parent::__construct($error, $code, $previous);
        $this->error_detail = $error_detail;
    }


    /**
     * リクエストエラー確認メソッド
     *
     * @return bool 無効なリクエストエラーならtrue, そうでなければfalse
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
     * ログイン画面表示要求エラー確認メソッド
     *
     * @return bool ログイン画面表示要求エラーならtrue, そうでなければfalse
     */
    public function loginRequired()
    {
        if (preg_match("/login_required/", $this->message)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 同意画面表示要求エラー確認メソッド
     *
     * @return bool 同意画面表示要求エラーならtrue, そうでなければfalse
     */
    public function consentRequired()
    {
        if (preg_match("/consent_required/", $this->message)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * レスポンスタイプエラー確認メソッド
     *
     * @return bool レスポンスタイプエラーならtrue, そうでなければfalse
     */
    public function unsupportedResponseType()
    {
        if (preg_match("/unsupported_response_type/", $this->message)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * クライアント認証エラー確認メソッド
     *
     * @return bool クライアント認証エラーならtrue, そうでなければfalse
     */
    public function unauthorizedClient()
    {
        if (preg_match("/unauthorized_client/", $this->message)) {
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
        return __CLASS__ . ": " . $this->message . " ( $this->error_detail )";
    }
}
