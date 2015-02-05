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

/** \file HttpClient.php
 *
 * \brief HTTP通信の機能を提供するクラスを定義しています.
 */

namespace YConnect\Util;

/**
 * \class HttpClientクラス
 *
 * \brief HTTP通信の機能を提供するクラスです.
 *
 * 各サーバのリクエストで用いられるHTTP通信の機能を提供するクラスです.
 */
class HttpClient
{
    /**
     * \private \brief curlインスタンス
     */
    private $ch = null;

    /**
     * \private \brief SSLチェックフラグ
     */
    private static $sslCheckFlag = true;

    /**
     * \private \brief 全レスポンスヘッダ情報
     */
    private $headers = array();

    /**
     * \private \brief レスポンスボディ
     */
    private $body = null;

    /**
     * \brief Curlインスタンス生成
     */
    public function __construct()
    {
        $this->ch = curl_init();
        //curl_setopt( $this->ch, CURLOPT_VERBOSE, 1 ); // 詳細情報出力
        //curl_setopt( $this->ch, CURLOPT_FAILONERROR, 1 );	// 400以上でなにもしない
        curl_setopt( $this->ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $this->ch, CURLOPT_HEADER, true );
        Logger::debug( "curl_init(" . get_class() . "::" . __FUNCTION__ . ")" );
    }

    /**
     * \brief Curlインスタンス削除
     */
    public function __destruct()
    {
        if( $this->ch != null ) {
            curl_close( $this->ch );
            $this->ch = null;
            Logger::debug( "curl_closed(" . get_class() . "::" . __FUNCTION__ . ")" );
        }
    }

    /**
     * \brief SSLチェック解除メソッド
     */
    public static function disableSSLCheck()
    {
        self::$sslCheckFlag = false;

        Logger::debug( "disable SSL check(" . get_class() . "::" . __FUNCTION__ . ")" );
    }

    /**
     * \brief ヘッダ設定メソッド
     * @param	$headers	ヘッダの配列
     */
    public function setHeader($headers = null)
    {
        if( $headers != null ) {
            curl_setopt( $this->ch, CURLOPT_HTTPHEADER, $headers );
        }

        Logger::debug( "added headers(" . get_class() . "::" . __FUNCTION__ . ")", $headers );
    }

    /**
     * \brief POSTリクエストメソッド
     * @param	$url	エンドポイントURL
     * @param	$data	パラメータ配列
     */
    public function requestPost($url, $data=null)
    {
        curl_setopt( $this->ch, CURLOPT_URL, $url );
        curl_setopt( $this->ch, CURLOPT_POST, 1 );
        curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $data );
        Logger::info( "curl url(" . get_class() . "::" . __FUNCTION__ . ")", $url );

        if( !self::$sslCheckFlag ) {
            curl_setopt( $this->ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $this->ch, CURLOPT_SSL_VERIFYHOST, false );
        }

        $result = curl_exec( $this->ch );
        $info   = curl_getinfo( $this->ch );

        if( !$result ) {
            Logger::error( "failed curl_exec(" . get_class() . "::" . __FUNCTION__ . ")" );
            Logger::error( "curl_errno: " . curl_errno( $this->ch ) );
            throw new \Exception( "failed curl_exec." );
        }

        $this->extractResponse( $result, $info );

        Logger::info( "curl_exec(" . get_class() . "::" . __FUNCTION__ . ")", $data );
        Logger::debug( "response body(" . get_class() . "::" . __FUNCTION__ . ")", $result );
    }

    /**
     * \brief GETリクエストメソッド
     * @param	$url	エンドポイントURL
     * @param	$data	パラメータ配列
     */
    public function requestGet($url, $data=null)
    {
        if( $data != null ) {
            $query = http_build_query( $data );
            $parse = parse_url( $url );
            if( !empty( $parse["query"] ) ) {
                $url .= '&' . $query;
            } else {
                $url .= '?' . $query;
            }
        }

        curl_setopt( $this->ch, CURLOPT_URL, $url );
        Logger::info( "curl url(" . get_class() . "::" . __FUNCTION__ . ")", $url );

        if( !self::$sslCheckFlag ) {
            curl_setopt( $this->ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $this->ch, CURLOPT_SSL_VERIFYHOST, false );
        }

        $result = curl_exec( $this->ch );
        $info   = curl_getinfo( $this->ch );

        if( !$result ) {
            Logger::error( "failed curl_exec(" . get_class() . "::" . __FUNCTION__ . ")" );
            Logger::error( "curl_errno: " . curl_errno( $this->ch ) );
            throw new \Exception( "failed curl_exec." );
        }

        $this->extractResponse( $result, $info );

        Logger::info( "curl_exec(" . get_class() . "::" . __FUNCTION__ . ")", $data );
        Logger::debug( "response body(" . get_class() . "::" . __FUNCTION__ . ")", $result );
    }

    /**
     * \brief PUTリクエストメソッド
     * @param	$url	エンドポイントURL
     * @param	$data	パラメータ配列
     */
    public function requestPut($url, $data=null)
    {
        curl_setopt( $this->ch, CURLOPT_URL, $url );
        curl_setopt( $this->ch, CURLOPT_CUSTOMREQUEST, "PUT" );
        curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $data );

        Logger::info( "curl url(" . get_class() . "::" . __FUNCTION__ . ")", $url );

        if( !self::$sslCheckFlag ) {
            curl_setopt( $this->ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $this->ch, CURLOPT_SSL_VERIFYHOST, false );
        }

        $result = curl_exec( $this->ch );
        $info   = curl_getinfo( $this->ch );

        if( !$result ) {
            Logger::error( "failed curl_exec(" . get_class() . "::" . __FUNCTION__ . ")" );
            Logger::error( "curl_errno: " . curl_errno( $this->ch ) );
            throw new \Exception( "failed curl_exec." );
        }

        $this->extractResponse( $result, $info );

        Logger::info( "curl_exec(" . get_class() . "::" . __FUNCTION__ . ")", $data );
        Logger::debug( "response body(" . get_class() . "::" . __FUNCTION__ . ")", $result );
    }

    /**
     * \brief DELETEリクエストメソッド
     * @param	$url	エンドポイントURL
     * @param	$data	パラメータ配列
     */
    public function requestDelete($url, $data=null)
    {
        curl_setopt( $this->ch, CURLOPT_URL, $url );
        curl_setopt( $this->ch, CURLOPT_CUSTOMREQUEST, "DELETE" );
        curl_setopt( $this->ch, CURLOPT_POSTFIELDS, $data );
        Logger::info( "curl url(" . get_class() . "::" . __FUNCTION__ . ")", $url );

        if( !self::$sslCheckFlag ) {
            curl_setopt( $this->ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $this->ch, CURLOPT_SSL_VERIFYHOST, false );
        }

        $result = curl_exec( $this->ch );
        $info   = curl_getinfo( $this->ch );

        if( !$result ) {
            Logger::error( "failed curl_exec(" . get_class() . "::" . __FUNCTION__ . ")" );
            Logger::error( "curl_errno: " . curl_errno( $this->ch ) );
            throw new \Exception( "failed curl_exec." );
        }

        $this->extractResponse( $result, $info );

        Logger::info( "curl_exec(" . get_class() . "::" . __FUNCTION__ . ")", $data );
        Logger::debug( "response body(" . get_class() . "::" . __FUNCTION__ . ")", $result );
    }

    /**
     * \brief 全レスポンスヘッダ取得メソッド
     */
    public function getResponseHeaders()
    {
        if( $this->headers != null ) {
            return $this->headers;
        } else {
            return false;
        }
    }

    /**
     * \brief レスポンスヘッダ取得メソッド
     * @param	$header_name	ヘッダフィールド
     */
    public function getResponseHeader($header_name)
    {
        if( array_key_exists( $header_name, $this->headers ) ) {
            return $this->headers[$header_name];
        } else {
            return null;
        }
    }

    /**
     * \brief レスポンスボディ取得メソッド
     */
    public function getResponseBody()
    {
        if( $this->body != null ) {
            return $this->body;
        } else {
            return null;
        }
    }

    /**
     * \brief レスポンス抽出メソッド
     *
     * レスポンスをヘッダとボディ別に抽出
     *
     * @param	$raw_response	レスポンス文字列
     */
    private function extractResponse($raw_response, $info)
    {
        // ヘッダとボディを分割
        $headers_raw = substr( $raw_response, 0, $info['header_size'] );
        $headers_raw = preg_replace( "/(\r\n\r\n)$/", "", $headers_raw );
        $body_raw    = substr( $raw_response, $info['header_size'] );

        // ヘッダを連想配列形式に変換
        $headers_raw_array = preg_split( "/\r\n/", $headers_raw );
        $headers_raw_array = array_map( "trim", $headers_raw_array );

        foreach( $headers_raw_array as $header_raw ) {

            if( preg_match( "/HTTP/", $header_raw ) ) {
                $headers_asoc_array[0] = $header_raw;
            } else {
                $tmp = preg_split( "/: /", $header_raw );
                $field = $tmp[0];
                $value = $tmp[1];
                $headers_asoc_array[$field] = $value;
            }

        }

        $this->headers = $headers_asoc_array;
        $this->body    = $body_raw;

        Logger::debug( "extracted headers(" . get_class() . "::" . __FUNCTION__ . ")", $this->headers );
        Logger::debug( "extracted body(" . get_class() . "::" . __FUNCTION__ . ")", $this->body );
    }
}
