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
 * ApiExceptionクラス
 *
 * Web API例外処理クラスです.
 */
class ApiException extends Exception
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
     * 無効なアクセストークンエラー確認メソッド
     *
     * @return bool 無効なアクセストークンエラーならtrue, そうでなければfalse
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
     * @return bool 無効なリクエストパラメータエラーならtrue, そうでなければfalse
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
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . ": " . $this->message . " ( $this->error_detail )";
    }
}
