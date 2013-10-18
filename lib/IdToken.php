<?php
/**
 * The MIT License (MIT)
 * 
 * Copyright (C) 2013 Yahoo Japan Corporation. All Rights Reserved. 
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

require_once "JWT.php";

/**
 * IdToken Data Object
 * requires JWT library ( https://github.com/luciferous/jwt )
 */
class IdToken
{
    private $required_keys = array('iss', 'aud', 'exp', 'user_id', 'nonce');

    private $json = NULL;
    private $jwt = '';

    /**
     * Constructor
     *
     * @param $data   JWT raw string or Object(stdClass)
     * @param $key    id_token secret key
     */
    public function __construct($data, $key)
    {
        if ( $data instanceof stdClass ) {
            $this->_checkFormat($data);
            $this->json = $data;
            $this->jwt = JWT::encode($data, $key, true);
        } elseif ( is_string($data) ) {
            $this->json = JWT::decode($data, $key, true);
            $this->_checkFormat($this->json);
            $this->jwt = $data;
        } else {
            throw new UnexpectedValueException('IdToken requires stdClass json object or JWT String');
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

    /**
     * check the required data of id_token
     * 
     * @param   object  JWT stdClass object
     */
    private function _checkFormat($obj)
    {
        foreach ( $this->required_keys as $rkey ) {
            if ( ! property_exists($obj, $rkey) )
                throw new UnexpectedValueException('Not a valid IdToken format'); 
        }
    }
}

/* vim:ts=4:sw=4:sts=0:tw=0:ft=php:set et: */
