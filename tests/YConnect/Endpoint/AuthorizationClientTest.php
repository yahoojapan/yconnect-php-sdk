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

namespace YConnect\Endpoint;

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionException;
use YConnect\Constant\ResponseType;
use YConnect\Credential\ClientCredential;

class AuthorizationClientTest extends PHPUnit_Framework_TestCase
{
    private $endpoint = "https://example.co.jp/";
    private $client_id = "sample~client~id";
    private $response_type = ResponseType::CODE;

    /**
     * @test
     */
    public function testRequestAuthorizationGrant()
    {
        $redirect_uri = "https://example.co.jp/callback";

        $expect = $this->endpoint . "?" . http_build_query(array(
                "scope" => "token address",
                "response_type" => $this->response_type,
                "client_id" => $this->client_id,
                "redirect_uri" => $redirect_uri
            ));

        $client_credential = new ClientCredential($this->client_id, "sample~client~secret");
        $client = new AuthorizationClientMock($this->endpoint, $client_credential, $this->response_type);
        $client->setScopes(array("token", "address"));

        $client->requestAuthorizationGrant($redirect_uri);

        $this->assertSame($expect, $client->redirect_uri);
    }

    /**
     * @test
     */
    public function testRequestAuthorizationGrantWithState()
    {
        $redirect_uri = "https://example.co.jp/callback";
        $state = "sampleState";

        $expect = $this->endpoint . "?" . http_build_query(array(
                "scope" => "token address",
                "response_type" => $this->response_type,
                "client_id" => $this->client_id,
                "redirect_uri" => $redirect_uri,
                "state" => $state
            ));

        $client_credential = new ClientCredential($this->client_id, "sample~client~secret");
        $client = new AuthorizationClientMock($this->endpoint, $client_credential, $this->response_type);
        $client->setScopes(array("token", "address"));

        $client->requestAuthorizationGrant($redirect_uri, $state);

        $this->assertSame($expect, $client->redirect_uri);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testSetScopes()
    {
        $client_credential = new ClientCredential($this->client_id, "sample~client~secret");
        $client = new AuthorizationClientMock($this->endpoint, $client_credential, $this->response_type);

        $client->setScopes(array("token", "address"));

        $params_field = (new ReflectionClass(AuthorizationClient::class))->getProperty('params');
        $params_field->setAccessible(true);

        $this->assertSame("token address", $params_field->getValue($client)["scope"]);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testSetParam()
    {
        $key = 'key';
        $val = 'val';

        $client_credential = new ClientCredential($this->client_id, "sample~client~secret");
        $client = new AuthorizationClientMock($this->endpoint, $client_credential, $this->response_type);

        $client->setParam($key, $val);

        $params_field = (new ReflectionClass(AuthorizationClient::class))->getProperty('params');
        $params_field->setAccessible(true);

        $this->assertSame($val, $params_field->getValue($client)[$key]);
    }

    /**
     * @test
     * @throws ReflectionException
     */
    public function testSetParams()
    {
        $key1 = 'key1';
        $key2 = 'key2';
        $val1 = 'val1';
        $val2 = 'val2';

        $client_credential = new ClientCredential($this->client_id, "sample~client~secret");
        $client = new AuthorizationClientMock($this->endpoint, $client_credential, $this->response_type);

        $client->setParam($key1, $val1);
        $client->setParams(array($key2 => $val2));

        $params_field = (new ReflectionClass(AuthorizationClient::class))->getProperty('params');
        $params_field->setAccessible(true);

        $this->assertSame($val1, $params_field->getValue($client)[$key1]);
        $this->assertSame($val2, $params_field->getValue($client)[$key2]);
    }
}

class AuthorizationClientMock extends AuthorizationClient
{
    public $redirect_uri;

    public function __construct($endpoint, $client_credential, $response_type = null)
    {
        parent::__construct($endpoint, $client_credential, $response_type);
    }

    protected function redirect($request_uri)
    {
        $this->redirect_uri = $request_uri;
    }
}
