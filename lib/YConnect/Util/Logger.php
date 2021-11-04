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

/** \file Logger.php
 *
 * \brief Loggerクラスです.
 */

namespace YConnect\Util;

/**
 * \class Loggerクラス
 *
 * \brief ログ機能を実装したクラスです.
 */
class Logger
{
    /**
     * ログレベル定数 debug
     */
    const DEBUG = 1;

    /**
     * ログレベル定数 info
     */
    const INFO = 2;

    /**
     * ログレベル定数 error
     */
    const ERROR = 3;

    /**
     * ログ出力方法定数 console
     */
    const CONSOLE_TYPE = "console";

    /**
     * ログ出力方法定数 log
     */
    const LOG_TYPE = "log";

    /**
     * @var string ログ出力方法
     */
    private static $log_type = self::LOG_TYPE;
    /**
     * @var string ログレベル
     */
    private static $log_level = self::ERROR;

    /**
     * @var string ログ出力先パス
     */
    private static $log_path = null;

    /**
     * ログ出力方法設定メソッド
     *
     * @param string $log_type ログ出力方法定数(CONSOLE_TYPE or LOG_TYPE)
     */
    public static function setLogType($log_type)
    {
        if (in_array($log_type, [self::CONSOLE_TYPE, self::LOG_TYPE])) {
            self::$log_type = $log_type;
            return;
        }
        self::$log_type = self::LOG_TYPE;
    }

    /**
     * ログレベル設定メソッド
     *
     * @param string $log_level ログレベル定数(DEBUG or INFO or ERROR)
     */
    public static function setLogLevel($log_level)
    {
        if (in_array($log_level, [self::DEBUG, self::INFO, self::ERROR])) {
            self::$log_level = $log_level;
            return;
        }
        self::$log_level = self::INFO;
    }

    /**
     * ログ出力先パス設定メソッド
     *
     * @param string $log_path ログ出力先パス
     */
    public static function setLogPath($log_path)
    {
        self::$log_path = $log_path;
    }

    /**
     * デバッグログ出力メソッド
     *
     * @param string $message ログメッセージ
     * @param object|null $object 対象オブジェクト
     */
    public static function debug($message, $object = null)
    {
        if (self::$log_level <= self::DEBUG) {
            self::outputLog("[YConnect] [DEBUG] " . $message, $object);
        }
    }

    /**
     * 情報ログ出力メソッド
     *
     * @param string $message ログメッセージ
     * @param object|null $object 対象オブジェクト
     */
    public static function info($message, $object = null)
    {
        if (self::$log_level <= self::INFO) {
            self::outputLog("[YConnect] [INFO] " . $message, $object);
        }
    }

    /**
     * エラーログ出力メソッド
     *
     * @param string $message ログメッセージ
     * @param object|null $object 対象オブジェクト
     */
    public static function error($message, $object = null)
    {
        if (self::$log_level <= self::ERROR) {
            self::outputLog("[YConnect] [ERROR] " . $message, $object);
        }
    }

    /**
     * 共通ログ出力メソッド
     *
     * @param string $message ログメッセージ
     * @param object|null $object 対象オブジェクト
     */
    private static function outputLog($message, $object = null)
    {
        if (self::$log_type === self::CONSOLE_TYPE) {
            echo $message."\n";
            if ($object != null) {
                echo print_r($object, true);
            }
            return;
        }
        if (self::$log_type === self::LOG_TYPE) {
            if (!self::$log_path) {
                error_log($message);
                if ($object != null) {
                    error_log(print_r($object, true));
                }
                return;
            }
            error_log($message . "\n", 3, self::$log_path);
            if ($object != null) {
                error_log(print_r($object, true), 3, self::$log_path);
            }
        }
    }
}
