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

// 例外クラス
require("OAuth2AuthorizationException.php");
require("OAuth2TokenException.php");
require("OAuth2ApiException.php");
require("IdTokenException.php");

// ユーティリティクラス
require("YConnectLogger.php");
require("HttpClient.php");
require("IdTokenUtil.php");

// クレデンシャル・トークンクラス
require("ClientCredential.php");
require("OAuth2BearerToken.php");
require("OAuth2RefreshToken.php");
require("IdToken.php");

// 定数定義クラス
require("OAuth2GrantType.php");
require("OAuth2ResponseType.php");
require("OIDConnectDisplay.php");
require("OIDConnectPrompt.php");
require("OIDConnectScope.php");

// 各エンドポイントクライアント
require("OAuth2TokenClient.php");
require("OAuth2AuthorizationCodeClient.php");
require("OAuth2ClientCredentialsClient.php");
require("OAuth2RefreshTokenClient.php");
require("OAuth2AuthorizationClient.php");
require("OAuth2ApiClient.php");
require("UserInfoClient.php");

// YConnectクライアントクラス
require("YConnectClient.php");
