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

use YConnect\Credential\PublicKeys;

/** \file YConnectJWT.php
 *
 * \brief JSON Web Token (JWT) for YConnect
 */

class JWT
{
    /**
     * @param string $jwt token
     * @param PublicKeys $public_keys public keys
     *
     * @return object The JWT's payload
     */
    public static function getDecodedToken($jwt, $public_keys)
    {
        $part = explode('.', $jwt);
        if(!is_array($part) || empty($part) || count($part) !== 3 ){
            throw new \UnexpectedValueException('invalid jwt format');
        }
        list($b64header, $b64payload, $b64sig) = $part;
        $header    = self::jsonDecode(self::urlDecode($b64header));
        $payload   = self::jsonDecode(self::urlDecode($b64payload));
        $signature = self::urlDecode($b64sig);

        if(!$header->kid) {
            throw new \UnexpectedValueException('header does not have kid parameter');
        }
        if(!$public_keys->getPublicKey($header->kid)) {
            throw new \UnexpectedValueException('public key for kid not found');
        }

        // verify sig
        $public_key = $public_keys->getPublicKey($header->kid);
        if(!self::verifySignature($b64header, $b64payload, $signature, $public_key)) {
            throw new \UnexpectedValueException('invalid jwt signature');
        }

        return $payload;
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
     * @param string $b64header Header part of JWT
     * @param string $b64payload Payload part of JWT
     * @param string $signature Signature part of JWT
     * @param string $publicKey
     *
     * @return bool When valid signature, return true.
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
        openssl_free_key($publicKeyId);
        if ($result !== 1) {
            // invalid signature
            return false;
        }
        return true;
    }
}
