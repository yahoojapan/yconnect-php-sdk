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

require_once("vendor/autoload.php");

use YConnect\Constant\OIDConnectDisplay;
use YConnect\Constant\OIDConnectPrompt;
use YConnect\Constant\OIDConnectScope;
use YConnect\Constant\ResponseType;
use YConnect\Credential\ClientCredential;
use YConnect\Exception\ApiException;
use YConnect\Exception\TokenException;
use YConnect\YConnectClient;

// アプリケーションID, シークレッvト
$client_id     = "YOUR_APPLICATION_ID";
$client_secret = "YOUR_SECRET";

// 各パラメータ初期化
$redirect_uri = "http://" . $_SERVER["SERVER_NAME"] . $_SERVER["PHP_SELF"];

// リクエストとコールバック間の検証用のランダムな文字列を指定してください
$state = "44Oq44Ki5YWF44Gr5L+644Gv44Gq44KL77yB";
// リプレイアタック対策のランダムな文字列を指定してください
$nonce = "5YOV44Go5aWR57SE44GX44GmSUTljqjjgavjgarjgaPjgabjgog=";
// 認可コード横取り攻撃対策文字列を指定してください
$plain_code_challenge = "E9Melhoa2OwvFrEMTJguCHaoeK1t8URWbuGJSstw-cM._~";

$response_type = ResponseType::CODE;
$scope = array(
    OIDConnectScope::OPENID,
    OIDConnectScope::PROFILE,
    OIDConnectScope::EMAIL,
    OIDConnectScope::ADDRESS
);
$display = OIDConnectDisplay::DEFAULT_DISPLAY;
$prompt = array(
    OIDConnectPrompt::DEFAULT_PROMPT
);

// クレデンシャルインスタンス生成
$cred = new ClientCredential($client_id, $client_secret);
// YConnectクライアントインスタンス生成
$client = new YConnectClient($cred);

// デバッグ用ログ出力
$client->enableDebugMode();

try {
    // Authorization Codeを取得
    $code_result = $client->getAuthorizationCode($state);

    if (!$code_result) {

        /*****************************
             Authorization Request
        *****************************/

        // Authorizationエンドポイントにリクエスト
        $client->requestAuth(
            $redirect_uri,
            $state,
            $nonce,
            $response_type,
            $scope,
            $display,
            $prompt,
            3600,
            $plain_code_challenge
        );
    } else {

        /****************************
             Access Token Request
        ****************************/

        // Tokenエンドポイントにリクエスト
        $client->requestAccessToken(
            $redirect_uri,
            $code_result,
            $plain_code_challenge
        );

        echo "<h1>Access Token Request</h1>";
        // アクセストークン, リフレッシュトークン, IDトークンを取得
        echo "ACCESS TOKEN : " . $client->getAccessToken() . "<br/><br/>";
        echo "REFRESH TOKEN: " . $client->getRefreshToken() . "<br/><br/>";
        echo "EXPIRATION   : " . $client->getAccessTokenExpiration() . "<br/><br/>";

        /*****************************
             Verification ID Token
        *****************************/

        // IDトークンを検証
        $client->verifyIdToken($nonce, $client->getAccessToken());
        echo "ID TOKEN: <br/>";
        echo "<pre>" . print_r($client->getIdToken(), true) . "</pre>";

        /************************
             UserInfo Request
        ************************/

        // UserInfoエンドポイントにリクエスト
        $client->requestUserInfo($client->getAccessToken());
        echo "<h1>UserInfo Request</h1>";
        echo "UserInfo: <br/>";
        // UserInfo情報を取得
        echo "<pre>" . print_r($client->getUserInfo(), true) . "</pre>";
    }
} catch (ApiException $ae) {
    // アクセストークンが有効期限切れであるかチェック
    if ($ae->invalidToken()) {

        /************************************
             Refresh Access Token Request
        ************************************/

        try {
            // 保存していたリフレッシュトークンを指定してください
            $refresh_token = "STORED_REFRESH_TOKEN";

            // Tokenエンドポイントにリクエストしてアクセストークンを更新
            $client->refreshAccessToken($refresh_token);
            echo "<h1>Refresh Access Token Request</h1>";
            echo "ACCESS TOKEN : " . $client->getAccessToken() . "<br/><br/>";
            echo "EXPIRATION   : " . $client->getAccessTokenExpiration();
        } catch (TokenException $te) {
            // リフレッシュトークンが有効期限切れであるかチェック
            if ($te->invalidGrant()) {
                // はじめのAuthorizationエンドポイントリクエストからやり直してください
                echo "<h1>Refresh Token has Expired</h1>";
            }

            echo "<pre>" . print_r($te, true) . "</pre>";
        } catch (Exception $e) {
            echo "<pre>" . print_r($e, true) . "</pre>";
        }
    } elseif ($ae->invalidRequest()) {
        echo "<h1>Invalid Request</h1>";
        echo "<pre>" . print_r($ae, true) . "</pre>";
    } else {
        echo "<h1>Other Error</h1>";
        echo "<pre>" . print_r($ae, true) . "</pre>";
    }
} catch (Exception $e) {
    echo "<pre>" . print_r($e, true) . "</pre>";
}
