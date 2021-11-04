<?php
/**
 * The MIT License (MIT)
 *
 * Copyright (C) 2021 Yahoo Japan Corporation. All Rights Reserved.
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

namespace YConnect;

use Exception;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionException;
use YConnect\Constant\GrantType;
use YConnect\Constant\OIDConnectDisplay;
use YConnect\Constant\OIDConnectPrompt;
use YConnect\Credential\BearerToken;
use YConnect\Credential\ClientCredential;
use YConnect\Credential\RefreshToken;
use YConnect\Endpoint\AuthorizationClient;
use YConnect\Endpoint\AuthorizationCodeClient;
use YConnect\Endpoint\PublicKeysClient;
use YConnect\Endpoint\RefreshTokenClient;
use YConnect\Exception\TokenException;
use YConnect\WebAPI\UserInfoClient;

class YConnectClientTest extends PHPUnit_Framework_TestCase
{
    private $client_id = "sample~client~id";
    private $client_secret = "sample~client~secret";
    private $client_cred;

    private $redirect_uri = "https://example.co.jp/callback";
    private $state = "sample_state";
    private $nonce = "sample_nonce";
    private $response_type = "code";
    private $scope = ["openid", "profile"];
    private $scope_str = "openid profile";
    private $display = OIDConnectDisplay::DEFAULT_DISPLAY;
    private $prompt = [OIDConnectPrompt::SELECT_ACCOUNT, OIDConnectPrompt::LOGIN];
    private $prompt_str = OIDConnectPrompt::SELECT_ACCOUNT . " " . OIDConnectPrompt::LOGIN;
    private $max_age = 3600;
    private $code_challenge_plain = "sample_code_challenge";
    private $code_challenge;

    /**
     * @throws ReflectionException
     */
    public function setUp()
    {
        $this->client_cred = new ClientCredential($this->client_id, $this->client_secret);

        $client = new YConnectClient($this->client_cred);
        $generate_code_challenge_method = (new ReflectionClass(YConnectClient::class))
            ->getMethod("generateCodeChallenge");
        $generate_code_challenge_method->setAccessible(true);
        $this->code_challenge = $generate_code_challenge_method->invoke($client, $this->code_challenge_plain);
    }

    /**
     * @test
     */
    public function testRequestAuthWithRequiredParameters()
    {
        $mock = $this->createMock(AuthorizationClient::class);
        $mock->expects($this->once())
            ->method('setParam')
            ->withConsecutive(
                ["nonce", $this->nonce]
            );

        $client = new YConnectClientMock($this->client_cred);
        $client->authorizationClient = $mock;

        $client->requestAuth($this->redirect_uri, $this->state, $this->nonce, $this->response_type);
    }

    /**
     * SDK v2.xで採用していたパラメータがv3.xでも有効であるかをテスト
     *
     * @test
     */
    public function testRequestAuthWithOptionalParameters()
    {
        $mock = $this->createMock(AuthorizationClient::class);
        $mock->expects($this->exactly(4))
            ->method('setParam')
            ->withConsecutive(
                ["nonce", $this->nonce],
                ["scope", $this->scope_str],
                ["display", $this->display],
                ["prompt", $this->prompt_str]
            );

        $client = new YConnectClientMock($this->client_cred);
        $client->authorizationClient = $mock;

        $client->requestAuth(
            $this->redirect_uri,
            $this->state,
            $this->nonce,
            $this->response_type,
            $this->scope,
            $this->display,
            $this->prompt
        );
    }

    /**
     * @test
     */
    public function testRequestAuthWithOptionalParametersIncludedCodeChallenge()
    {
        $mock = $this->createMock(AuthorizationClient::class);
        $mock->expects($this->exactly(7))
            ->method('setParam')
            ->withConsecutive(
                ["nonce", $this->nonce],
                ["scope", $this->scope_str],
                ["display", $this->display],
                ["prompt", $this->prompt_str],
                ["max_age", $this->max_age],
                ["code_challenge", $this->code_challenge],
                ["code_challenge_method", "S256"]
            );

        $client = new YConnectClientMock($this->client_cred);
        $client->authorizationClient = $mock;

        $client->requestAuth(
            $this->redirect_uri,
            $this->state,
            $this->nonce,
            $this->response_type,
            $this->scope,
            $this->display,
            $this->prompt,
            $this->max_age,
            $this->code_challenge_plain
        );
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testCheckResponseReturnsTrue()
    {
        $client = new YConnectClientMock($this->client_cred);
        $check_response_method = (new ReflectionClass(YConnectClient::class))->getMethod("checkResponse");
        $check_response_method->setAccessible(true);

        $_GET["state"] = $this->state;

        $this->assertTrue($check_response_method->invoke($client, $this->state));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testCheckResponseReturnsFalse()
    {
        $client = new YConnectClientMock($this->client_cred);
        $check_response_method = (new ReflectionClass(YConnectClient::class))->getMethod("checkResponse");
        $check_response_method->setAccessible(true);

        $this->assertFalse($check_response_method->invoke($client, $this->state));
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testCheckResponseThrowsTokenException()
    {
        $client = new YConnectClientMock($this->client_cred);
        $check_response_method = (new ReflectionClass(YConnectClient::class))->getMethod("checkResponse");
        $check_response_method->setAccessible(true);

        $this->expectException(TokenException::class);
        $this->expectExceptionMessage("not_matched_state");

        $_GET["state"] = $this->state;

        $check_response_method->invoke($client, "invalid_state");
    }

    /**
     * @test
     * @throws TokenException
     */
    public function testGetAuthorizationCode()
    {
        $code = "sample_code";
        $client = new YConnectClientMock($this->client_cred);

        $_GET["code"] = $code;
        $_GET["state"] = $this->state;

        $this->assertSame($code, $client->getAuthorizationCode($this->state));
    }

    /**
     * @test
     * @throws TokenException
     */
    public function testGetAuthorizationCodeReturnsFalseByStateMissed()
    {
        $code = "sample_code";
        $client = new YConnectClientMock($this->client_cred);

        $_GET["code"] = $code;

        $this->assertFalse($client->getAuthorizationCode($this->state));
    }

    /**
     * @test
     * @throws TokenException
     */
    public function testGetAuthorizationCodeReturnsFalseByCodeMissed()
    {
        $client = new YConnectClientMock($this->client_cred);

        $_GET["state"] = $this->state;

        $this->assertFalse($client->getAuthorizationCode($this->state));
    }

    /**
     * @test
     * @throws TokenException
     */
    public function testGetAuthorizationCodeThrowsTokenException()
    {
        $error = "error_sample";
        $error_description = "error_description_sample";
        $error_code = 400;

        $client = new YConnectClientMock($this->client_cred);

        $_GET["state"] = $this->state;
        $_GET["error"] = $error;
        $_GET["error_description"] = $error_description;
        $_GET["error_code"] = $error_code;

        $this->expectException(TokenException::class);
        $this->expectExceptionMessage($error);

        $client->getAuthorizationCode($this->state);
    }

    /**
     * @test
     * @throws Exception
     */
    public function testRequestAccessToken()
    {
        $client = new YConnectClientMock($this->client_cred);
        $code = "code_sample";
        $access_token = "access_token_sample";
        $expiration = 3600;
        $bearer_token = new BearerToken($access_token, $expiration);
        $refresh_token = "refresh_token_sample";
        $id_token = "id_token_sample";

        $public_keys_client_mock = $this->createMock(PublicKeysClient::class);
        $public_keys_client_mock->expects($this->once())
            ->method("fetchPublicKeys");
        $public_keys_client_mock->expects($this->once())
            ->method("getResponse")
            ->willReturn(json_encode(array(
                "kid" => "public_key_sample"
            )));
        $client->publicKeysClient = $public_keys_client_mock;

        $authorization_code_client_mock = $this->createMock(AuthorizationCodeClient::class);
        $authorization_code_client_mock->expects($this->once())
            ->method("setParams")
            ->with(array(
                "grant_type" => GrantType::AUTHORIZATION_CODE,
                "code" => $code
            ));
        $authorization_code_client_mock->expects($this->once())
            ->method("fetchToken");
        $authorization_code_client_mock->expects($this->once())
            ->method("getAccessToken")
            ->willReturn($bearer_token);
        $authorization_code_client_mock->expects($this->once())
            ->method("getRefreshToken")
            ->willReturn(new RefreshToken($refresh_token));
        $authorization_code_client_mock->expects($this->once())
            ->method("getIdToken")
            ->willReturn($id_token);
        $client->authorizationCodeClient = $authorization_code_client_mock;

        $client->requestAccessToken($this->redirect_uri, $code);

        $this->assertSame($access_token, $client->getAccessToken());
        $this->assertSame($refresh_token, $client->getRefreshToken());
        $this->assertSame($expiration, $client->getAccessTokenExpiration());
        $this->assertSame($id_token, $client->getIdToken());
    }

    /**
     * @test
     * @throws Exception
     */
    public function testRequestAccessTokenWithCodeVerifier()
    {
        $client = new YConnectClientMock($this->client_cred);
        $code = "code_sample";
        $code_verifier = "code_verifier_sample";
        $access_token = "access_token_sample";
        $expiration = 3600;
        $bearer_token = new BearerToken($access_token, $expiration);
        $refresh_token = "refresh_token_sample";
        $id_token = "id_token_sample";

        $public_keys_client_mock = $this->createMock(PublicKeysClient::class);
        $public_keys_client_mock->expects($this->once())
            ->method("fetchPublicKeys");
        $public_keys_client_mock->expects($this->once())
            ->method("getResponse")
            ->willReturn(json_encode(array(
                "kid" => "public_key_sample"
            )));
        $client->publicKeysClient = $public_keys_client_mock;

        $authorization_code_client_mock = $this->createMock(AuthorizationCodeClient::class);
        $authorization_code_client_mock->expects($this->once())
            ->method("setParams")
            ->with(array(
                "grant_type" => GrantType::AUTHORIZATION_CODE,
                "code" => $code
            ));
        $authorization_code_client_mock->expects($this->once())
            ->method("setParam")
            ->with("code_verifier", $code_verifier);
        $authorization_code_client_mock->expects($this->once())
            ->method("fetchToken");
        $authorization_code_client_mock->expects($this->once())
            ->method("getAccessToken")
            ->willReturn($bearer_token);
        $authorization_code_client_mock->expects($this->once())
            ->method("getRefreshToken")
            ->willReturn(new RefreshToken($refresh_token));
        $authorization_code_client_mock->expects($this->once())
            ->method("getIdToken")
            ->willReturn($id_token);
        $client->authorizationCodeClient = $authorization_code_client_mock;

        $client->requestAccessToken($this->redirect_uri, $code, $code_verifier);

        $this->assertSame($access_token, $client->getAccessToken());
        $this->assertSame($refresh_token, $client->getRefreshToken());
        $this->assertSame($expiration, $client->getAccessTokenExpiration());
        $this->assertSame($id_token, $client->getIdToken());
    }

    /**
     * @test
     * @throws TokenException
     */
    public function testRefreshAccessToken()
    {
        $client = new YConnectClientMock($this->client_cred);

        $access_token = "access_token_sample";
        $expiration = 3600;
        $bearer_token = new BearerToken($access_token, $expiration);

        $refresh_token_client_mock = $this->createMock(RefreshTokenClient::class);
        $refresh_token_client_mock->expects($this->once())
            ->method("fetchToken");
        $refresh_token_client_mock->expects($this->once())
            ->method("getAccessToken")
            ->willReturn($bearer_token);
        $client->refreshTokenClient = $refresh_token_client_mock;

        $client->refreshAccessToken("refresh_token_sample");

        $this->assertSame($access_token, $client->getAccessToken());
        $this->assertSame($expiration, $client->getAccessTokenExpiration());
    }

    /**
     * @test
     * @throws Exception
     */
    public function testRequestUserInfo()
    {
        $client = new YConnectClientMock($this->client_cred);

        $user_info = (object)array(
            "sub" => "sample_ppid",
            "name" => "sample name"
        );

        $user_info_client_mock = $this->createMock(UserInfoClient::class);
        $user_info_client_mock->expects($this->once())
            ->method("fetchUserInfo");
        $user_info_client_mock->expects($this->once())
            ->method("getUserInfo")
            ->willReturn($user_info);
        $client->userInfoClient = $user_info_client_mock;

        $client->requestUserInfo("access_token_sample");

        $this->assertEquals($user_info, $client->getUserInfo());
    }
}

class YConnectClientMock extends YConnectClient
{
    public $authorizationClient;
    public $publicKeysClient;
    public $authorizationCodeClient;
    public $refreshTokenClient;
    public $userInfoClient;

    protected function getAuthorizationClient($endpoint, $client_cred, $response_type)
    {
        return $this->authorizationClient;
    }

    protected function getPublicKeysClient($endpoint)
    {
        return $this->publicKeysClient;
    }

    protected function getAuthorizationCodeClient($endpoint, $client_cred, $code, $redirect_uri, $public_keys_json)
    {
        return $this->authorizationCodeClient;
    }

    protected function getRefreshTokenClient($endpoint, $client_cred, $refresh_token)
    {
        return $this->refreshTokenClient;
    }

    protected function getUserInfoClient($endpoint, $access_token)
    {
        return $this->userInfoClient;
    }
}
