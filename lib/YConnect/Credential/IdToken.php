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

namespace YConnect\Credential;

use YConnect\Util\JWT;
use YConnect\Exception\IdTokenException;
use YConnect\Util\Logger;

/**
 * IdToken Data Object
 * requires JWT library ( https://github.com/luciferous/jwt )
 */
class IdToken
{
    private $required_keys = array('iss', 'aud', 'exp', 'user_id', 'nonce');

    private $json = NULL;
    private $jwt = '';

    // for verify
    private static $issuer = "https://auth.login.yahoo.co.jp";

    /**
     * Constructor
     *
     * @param $data   JWT raw string or Object(stdClass)
     * @param $key    id_token secret key
     */
    public function __construct($data, $key)
    {
        if ( $data instanceof \stdClass ) {
            $this->_checkFormat($data);
            $this->json = $data;
            $this->jwt = JWT::getEncodedToken($data, $key);
        } elseif ( is_string($data) ) {
            $this->json = JWT::getDecodedToken($data, $key);
            $this->_checkFormat($this->json);
            $this->jwt = $data;
        } else {
            throw new \UnexpectedValueException('IdToken requires stdClass json object or JWT String');
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
    public function getUserId()
    {
        return $this->json->user_id;
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

    public static function verify($object, $auth_nonce, $client_id, $acceptable_range = 600)
    {
        // Is iss equal to issuer ?
        if ( self::$issuer != $object->iss )
            throw new IdTokenException( "Invalid issuer.", "The issuer did not match.({$object->iss})" );

        // Is nonce equal to this nonce (was issued at the request authorization) ?
        if ( $auth_nonce != $object->nonce )
            throw new IdTokenException( "Not match nonce.", "The nonce did not match.({$auth_nonce}, {$object->nonce})" );

        // Is aud equal to the client_id (Application ID) ?  if ( $client_id != $object->aud )
        if ( $client_id != $object->aud )
            throw new IdTokenException( "Invalid audience.", "The client id did not match.({$object->aud})" );

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

}
