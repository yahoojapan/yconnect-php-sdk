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

/** \file IdToken.php
 *
 * \brief ID Tokenを保持するクラスを定義しています.
 */

namespace YConnect\Credential;

use YConnect\Util\JWT;
use YConnect\Exception\IdTokenException;
use YConnect\Util\Logger;

/**
 * \class IdTokenクラス
 *
 * \brief ID Tokenを保持するクラスです.
 */
class IdToken
{
    private $required_keys = array('iss', 'sub', 'aud', 'exp', 'iat', 'nonce');

    private $json = NULL;
    private $jwt = '';

    // for verify
    private static $issuer = "https://auth.login.yahoo.co.jp/yconnect/v2";

    /**
     * Constructor
     *
     * @param string $data   JWT raw string
     * @param PublicKeys $public_keys    Map of public key
     */
    public function __construct($data, $public_keys)
    {
        if ( is_string($data) ) {
            $this->json = JWT::getDecodedToken($data, $public_keys);
            $this->_checkFormat($this->json);
            $this->jwt = $data;
        } else {
            throw new \UnexpectedValueException('IdToken requires JWT String');
        }
    }

    public function __clone()
    {
        $this->json = clone $this->json;
    }

    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return JWT raw string
     */
    public function toString()
    {
        return $this->jwt;
    }

    /**
     * @return user_id
     */
    public function getSub()
    {
        return $this->json->sub;
    }

    /**
     * @return expiration timestamp
     */
    public function getExpiration()
    {
        return $this->json->exp;
    }

    /**
     * @return true if expired
     */
    public function isExpired()
    {
        return ( $this->json->exp < time() );
    }

    /**
     * @return id token type stdClass
     */
    public function getIdToken()
    {
        return $this->json;
    }

    public static function verify($object, $auth_nonce, $client_id, $access_token, $acceptable_range = 600)
    {
        // Is iss equal to issuer ?
        if ( self::$issuer !== $object->iss )
            throw new IdTokenException( "Invalid issuer.", "The issuer did not match.({$object->iss})" );

        // Is nonce equal to this nonce (was issued at the request authorization) ?
        if ( $auth_nonce !== $object->nonce )
            throw new IdTokenException( "Not match nonce.", "The nonce did not match.({$auth_nonce}, {$object->nonce})" );

        // Is aud equal to the client_id (Application ID) ?  if ( $client_id != $object->aud )
        if ( !in_array($client_id, $object->aud) )
            throw new IdTokenException( "Invalid audience.", "The client id did not match.({$object->aud})" );

        if ($object->at_hash) {
            $hash = self::generateHash($access_token);
            if ($hash !== $object->at_hash )
                throw new IdTokenException( "Invalid at_hash.", "The at_hash did not match.({$object->at_hash})" );
        }

        // Is corrent time less than exp ?
        if ( time() > $object->exp )
            throw new IdTokenException( "Expired ID Token.", "Re-issue Id Token.({$object->exp})" );

        Logger::debug( "current time: " . time() . ", exp: {$object->exp}(" . get_class() . "::" . __FUNCTION__ . ")" );

        // prevent attacks
        $time_diff = time() - $object->iat;
        if ( $time_diff > $acceptable_range )
            throw new IdTokenException( "Over acceptable range.", "This access has expired possible.({$time_diff} sec)" );

        Logger::debug( "current time - iat = {$time_diff}, current time: " . time() .
            ", iat: {$object->iat}(" . get_class() . "::" . __FUNCTION__ . ")" );

        return true;
    }

    /**
     * check the required data of id_token
     *
     * @param   object  JWT stdClass object
     */
    private function _checkFormat($obj)
    {
        foreach ( $this->required_keys as $rkey ) {
            if ( ! property_exists($obj, $rkey) )
                throw new \UnexpectedValueException('Not a valid IdToken format');
        }
    }

    /**
     * generate hash for at_hash
     *
     * @param $value
     * @return mixed
     */
    private static function generateHash($value)
    {
        $hash = hash('sha256', $value, true);
        $length = strlen($hash) / 2;
        $halfOfHash = substr($hash, 0, $length);
        return str_replace('=', '', strtr(base64_encode($halfOfHash), '+/', '-_'));
    }

}
