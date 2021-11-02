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

/** \file OIDConnectDisplay.php
 *
 * \brief displayの列挙型クラスです.
 */
namespace YConnect\Constant;

/**
 * \class OIDConnectDisplayクラス
 *
 * \brief displayの列挙型クラスです.
 */
class OIDConnectDisplay
{
    /**
     * \public \brief empty string: ユーザーエージェント判定によるテンプレート出し分け
     */
    const DEFAULT_DISPLAY = "page";

    /**
     * \public \brief page: PC版テンプレート
     */
    const PC = "page";

    /**
     * \public \brief touch: スマートフォン版テンプレート
     */
    const SMART_PHONE = "touch";

    /**
     * \public \brief popup: ポップアップ版テンプレート
     */
    const POPUP = "popup";

    /**
     * \public \brief inapp: ネイティブアプリ版テンプレート
     */
    const NATIVE_APP = "inapp";
}
