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

/** \file YConnectJWT.php
 *
 * \brief JSON Web Token (JWT) for YConnect
 */

class JWT
{
    /**
     * @param string $jwt token
     * @param string $key secret key
     *
     * @return object The JWT's payload
     */
    public static function getDecodedToken($jwt, $key)
    {
        $part = explode('.', $jwt);
        if(!is_array($part) || empty($part) || count($part) !== 3 ){
            throw new \UnexpectedValueException('invalid jwt format');
        }
        list($b64header, $b64payload, $b64sig) = $part;
        $payload   = self::jsonDecode(self::urlDecode($b64payload));
        $signature = self::urlDecode($b64sig);

        // verify sig
        if ($signature != hash_hmac('sha256', "$b64header.$b64payload", $key, true)) {
            throw new \UnexpectedValueException('invalid jwt signature');
        }
        return $payload;
    }

    /**
     * @param object|array $payload
     * @param string       $key     secret key
     *
     * @return string JWT
     */
    public static function getEncodedToken($payload, $key)
    {
        // support only JWT typ and HS256 algorithm
        $header = array('typ' => 'JWT', 'alg' => 'HS256');

        $token  = self::urlEncode(self::jsonEncode($header)).".";
        $token .= self::urlEncode(self::jsonEncode($payload));
        $signature = hash_hmac('sha256', $token, $key, true);
        $token .= ".".self::urlEncode($signature);

        return $token;
    }

    /**
     * @param string base64 encoded
     *
     * @return decoded string
     */
    public static function urlDecode($str)
    {
        $dstr = strtr($str, '-_', '+/');
        // padding =
        $paddinglen = (4 - (strlen($dstr) % 4));
        if ($paddinglen) {
            $dstr .= str_repeat('=', $paddinglen);
        }
        return base64_decode($dstr);

    }

    /**
     * @param string
     *
     * @return string base64 encoded
     */
    public static function urlEncode($str)
    {
        return str_replace('=', '', strtr(base64_encode($str), '+/', '-_'));
    }

    /**
     * @param string $data JSON
     *
     * @return object Object representation of JSON string
     */
    public static function jsonDecode($data)
    {
        $obj = json_decode($data);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            throw new \UnexpectedValueException("JSON decode error: $errno");
        } elseif ($obj === 'null') {
            throw new \UnexpectedValueException('Failed to json_decode');
        }
        return $obj;
    }

    /**
     * @param object|array $data
     *
     * @return string JSON representation of the PHP object or array
     */
    public static function jsonEncode($data)
    {
        $json = json_encode($data);
        if (function_exists('json_last_error') && $errno = json_last_error()) {
            throw new \UnexpectedValueException("JSON encode error: $errno");
        } elseif ($json === 'null') {
            throw new \UnexpectedValueException('Failed to json_encode');
        }
        return $json;
    }
}
