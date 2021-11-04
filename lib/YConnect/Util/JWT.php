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

namespace YConnect\Util;

use UnexpectedValueException;
use YConnect\Credential\PublicKeys;

/**
 * JWTクラス
 *
 * JSON Web Token (JWT)に関する操作を提供するクラスです.
 */
class JWT
{
    /**
     * IDトークンをデコード
     *
     * @param string $jwt JWT形式のIDトークン
     * @param PublicKeys $public_keys 公開鍵リスト
     *
     * @return object JWTのペイロード部のオブジェクト
     */
    public static function getDecodedToken($jwt, $public_keys)
    {
        $part = explode('.', $jwt);
        if (!is_array($part) || empty($part) || count($part) !== 3) {
            throw new UnexpectedValueException('invalid jwt format');
        }
        list($b64header, $b64payload, $b64sig) = $part;
        $header    = self::jsonDecode(self::urlDecode($b64header));
        $payload   = self::jsonDecode(self::urlDecode($b64payload));
        $signature = self::urlDecode($b64sig);

        if (!isset($header->kid)) {
            throw new UnexpectedValueException('header does not have kid parameter');
        }
        if (!$public_keys->getPublicKey($header->kid)) {
            throw new UnexpectedValueException('public key for kid not found');
        }

        // verify sig
        $public_key = $public_keys->getPublicKey($header->kid);
        if (!self::verifySignature($b64header, $b64payload, $signature, $public_key)) {
            throw new UnexpectedValueException('invalid jwt signature');
        }

        return $payload;
    }

    /**
     * 文字列をbase64URLデコード
     *
     * @param string base64URLエンコードされた文字列
     * @return string base64URLデコードされた文字列
     */
    public static function urlDecode($str)
    {
        $decoded_str = strtr($str, '-_', '+/');
        // padding =
        $padding_length = (4 - (strlen($decoded_str) % 4));
        if ($padding_length) {
            $decoded_str .= str_repeat('=', $padding_length);
        }
        return base64_decode($decoded_str);
    }

    /**
     * JSON文字列をオブジェクトに変換
     *
     * @param string $data JSON文字列
     * @return object JSON文字列からデコードしたオブジェクト
     */
    public static function jsonDecode($data)
    {
        $obj = json_decode($data);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            throw new UnexpectedValueException("JSON decode error: $errno");
        } elseif ($obj === 'null') {
            throw new UnexpectedValueException('Failed to json_decode');
        }
        return $obj;
    }

    /**
     * JWTのシグネチャを検証
     *
     * @param string $b64header JWTのヘッダ部(base64URLエンコード)
     * @param string $b64payload JWTのペイロード部(base64URLエンコード)
     * @param string $signature JWTのシグネチャ部(base64URLデコード済)
     * @param string $publicKey 公開鍵
     *
     * @return bool 検証が成功すればtrue, そうでなければfalse
     */
    private static function verifySignature($b64header, $b64payload, $signature, $publicKey)
    {
        $data = $b64header . '.' . $b64payload;
        $publicKeyId = openssl_pkey_get_public($publicKey);
        if (!$publicKeyId) {
            // failed to get public key resource
            return false;
        }
        $result = openssl_verify($data, $signature, $publicKeyId, 'RSA-SHA256');

        // PHP 8.0.0 以降は自動でリソースが開放されるため非推奨になった
        // PHP 8.0.0 未満は開放が必要なため呼び出す
        if (version_compare(PHP_VERSION, '8.0.0', '<')) {
            openssl_free_key($publicKeyId);
        }

        if ($result !== 1) {
            // invalid signature
            return false;
        }
        return true;
    }
}
