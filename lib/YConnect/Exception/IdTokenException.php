<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (C) 2014 Yahoo Japan Corporation. All Rights Reserved.
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

/** \file IdTokenException.php
 *
 * \brief IDトークン例外処理クラスを定義しています.
 */

namespace YConnect\Exception;

/**
 * \class IdTokenExceptionクラス
 *
 * \brief IDトークン例外処理クラスです.
 *
 * IDトークン例外処理クラスです.
 */
class IdTokenException extends Exception
{
    /**
     * \brief エラー概要
     */
    public $error = null;

    /**
     * \brief エラー詳細
     */
    public $error_desc = null;

    /**
     * \brief インスタンス生成
     *
     * @param	$error	エラー概要
     * @param	$error_desc	エラー詳細
     * @param	$code
     */
    // $previous 5.3以降追加
    // public function __construct($message, $code = 0, Exception $previous = null) {
    public function __construct($error, $error_desc = "", $code = 0)
    {
        parent::__construct( $error, $code );

        $this->error      = $error;
        $this->error_desc = $error_desc;
    }

    public function __toString()
    {
        $str = __CLASS__ . " (" . $this->code . ") : " . $this->message . ", ";
        $str .= "error: " . $this->error . ", error_desc: " .$this->error_desc;

        return $str;
    }
}
