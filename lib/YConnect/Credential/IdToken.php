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

use UnexpectedValueException;
use YConnect\Exception\IdTokenException;
use YConnect\Util\JWT;
use YConnect\Util\Logger;

/**
 * IdTokenクラス
 *
 * ID Tokenを保持するクラスです.
 */
class IdToken
{
    /**
     * @var string[] IDトークンに含まれている必要があるキー
     */
    private $required_keys = array('iss', 'sub', 'aud', 'exp', 'iat', 'nonce');

    /**
     * @var object|null IDトークンのペイロード部のオブジェクト
     */
    private $json = null;

    /**
     * @var string JWT
     */
    private $jwt = '';

    /**
     * @var string IDトークンの発行元
     * @see https://openid.net/specs/openid-connect-core-1_0.html#Terminology OIDC仕様書
     */
    private static $issuer = "https://auth.login.yahoo.co.jp/yconnect/v2";

    /**
     * コンストラクタ
     *
     * @param string $data 文字列形式のJWT
     * @param PublicKeys $public_keys 公開鍵リスト
     */
    public function __construct($data, $public_keys)
    {
        if (is_string($data)) {
            $this->json = JWT::getDecodedToken($data, $public_keys);
            $this->checkFormat($this->json);
            $this->jwt = $data;
        } else {
            throw new UnexpectedValueException('IdToken requires JWT String');
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
     * @return string 文字列形式のJWT
     */
    public function toString()
    {
        return $this->jwt;
    }

    /**
     * @return string ユーザ識別子
     */
    public function getSub()
    {
        return $this->json->sub;
    }

    /**
     * @return int IDトークンの有効期限のUNIXタイムスタンプ
     */
    public function getExpiration()
    {
        return $this->json->exp;
    }

    /**
     * @return bool 有効期限切れならばtrue, そうでなければfalse
     */
    public function isExpired()
    {
        return ($this->json->exp < time());
    }

    /**
     * @return object IDトークン
     */
    public function getIdToken()
    {
        return $this->json;
    }

    /**
     * IDトークン検証
     *
     * @param object $object IDトークンのオブジェクト
     * @param string $auth_nonce nonce
     * @param string $client_id クライアントID
     * @param string $access_token アクセストークン
     * @param int $acceptable_range IDトークン発行時間の許容期間(sec)
     * @return bool 検証が成功したときtrue
     * @throws IdTokenException 検証が失敗したときに発生
     */
    public static function verify($object, $auth_nonce, $client_id, $access_token, $acceptable_range = 600)
    {
        // Is iss equal to issuer ?
        if (self::$issuer !== $object->iss) {
            throw new IdTokenException("Invalid issuer.", "The issuer did not match.($object->iss)");
        }

        // Is nonce equal to this nonce (was issued at the request authorization) ?
        if ($auth_nonce !== $object->nonce) {
            throw new IdTokenException("Not match nonce.", "The nonce did not match.($auth_nonce, $object->nonce)");
        }

        // Is aud equal to the client_id (Application ID) ?  if ( $client_id != $object->aud )
        if (!in_array($client_id, $object->aud)) {
            throw new IdTokenException("Invalid audience.", "No client id exists in aud.({$object->aud[0]})");
        }

        if (isset($object->at_hash)) {
            $hash = self::generateHash($access_token);
            if ($hash !== $object->at_hash) {
                throw new IdTokenException("Invalid at_hash.", "The at_hash did not match.($object->at_hash)");
            }
        }

        // Is current time less than exp ?
        if (time() > $object->exp) {
            throw new IdTokenException("Expired ID Token.", "Re-issue Id Token.($object->exp)");
        }

        Logger::debug("current time: " . time() . ", exp: $object->exp(" . get_class() . "::" . __FUNCTION__ . ")");

        // prevent attacks
        $time_diff = time() - $object->iat;
        if ($time_diff > $acceptable_range) {
            throw new IdTokenException("Over acceptable range.", "This access has expired possible.($time_diff sec)");
        }

        Logger::debug("current time - iat = $time_diff, current time: " . time() .
            ", iat: $object->iat(" . get_class() . "::" . __FUNCTION__ . ")");

        return true;
    }

    /**
     * IDトークンに必要なデータが入っているか確認
     *
     * @param object ペイロードのオブジェクト
     */
    private function checkFormat($obj)
    {
        foreach ($this->required_keys as $required_key) {
            if (!property_exists($obj, $required_key)) {
                throw new UnexpectedValueException('Not a valid IdToken format');
            }
        }
    }

    /**
     * at_hashを生成
     *
     * @param string $value 生成元文字列
     * @return string at_hash
     */
    private static function generateHash($value)
    {
        $hash = hash('sha256', $value, true);
        $length = strlen($hash) / 2;
        $halfOfHash = substr($hash, 0, $length);
        return str_replace('=', '', strtr(base64_encode($halfOfHash), '+/', '-_'));
    }
}
