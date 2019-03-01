Yahoo! ID連携 SDK for PHP
=======
Yahoo! ID連携（YConnect）のAuthorization Codeフローを実装するためのライブラリーです。  
実装に必要なクラスファイルが定義されています。 

### 使用API
* YConnect
  * http://developer.yahoo.co.jp/yconnect/server_app/explicit/
* UserInfo API
  * http://developer.yahoo.co.jp/yconnect/userinfo.html
* BillingAddress API
  * http://fastpay.yahooapis.jp/v1/address

### 構成環境
* PHP 5.3 （5.3.x）以降（curl、json関連のパッケージ必須）
* curl 7.34.0以降
* openssl 1.0.1以降

### 利用方法
#### Composerを利用する場合
Composerをインストールしてください。

```
$ curl -s http://getcomposer.org/installer | php
```

以下のようにcomposer.jsonを作成してください。

```
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/yahoojapan/yconnect-php-sdk"
        }
    ],
    "require": {
        "yahoojapan/yconnect-php-sdk": "dev-master"
    }
}
```

ライブラリーをインストールします。

```
$ php composer.phar install
```

autoloaderを読み込んでください。

```
require_once("vendor/autoload.php");
```

ライブラリーの使い方についてはサンプルコードをご参照ください。

Composerについては以下の外部サイトを参照してください。
* https://getcomposer.org/

#### ダウンロードする場合
libディレクトリをinclude_pathに設定してrequireあるいはincludeしてご利用ください。

### BillingAddress APIの利用方法
BillingAddress APIの利用には、Yahoo!ウォレット FastPay( https://fastpay.yahoo.co.jp )の登録が必要です。
利用までのフローを記載したページを用意しましたので、以下を参照してください。
* https://fastpay.yahoo.co.jp/docs/guide_address

### License
本ライブラリおよびサンプルコード等は MIT License にて提供しています。  
詳しくは LICENSE をご覧ください。

### Version
2.2.2

### Pull request に関して
現在 Contributor License Agreement（CLA）を準備しています。  
CLA に同意していただくまでは Pull request しても必ず受け付けられるわけではありません。   
準備が整うまでお待ちください。

