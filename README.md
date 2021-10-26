Yahoo! ID連携 SDK for PHP
=======
Yahoo! ID連携（YConnect）のAuthorization Codeフローを実装するためのライブラリーです。  
実装に必要なクラスファイルが定義されています。 

### 使用API
* YConnect
  * http://developer.yahoo.co.jp/yconnect/server_app/explicit/
* UserInfo API
  * http://developer.yahoo.co.jp/yconnect/userinfo.html

### 構成環境
* PHP 5.6 （5.6.x）以降（curl、json関連のパッケージ必須）
* curl 7.52.1以降
* openssl 1.1.0以降

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

#### Yahoo! ID連携 v1を利用する場合
**Yahoo! ID連携 v1は 2022年4月27日にクローズ予定です。
詳しくは [こちらのお知らせ](https://developer.yahoo.co.jp/changelog/2021-08-03-yconnect1.html)
をご確認ください。**

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
        "yahoojapan/yconnect-php-sdk": "dev-master2.2"
    }
}
```

### 詳細
本ライブラリの詳細に関しては以下のサイトを参照してください。  
https://developer.yahoo.co.jp/yconnect/v2/php_sdk/

### License
本ライブラリおよびサンプルコード等は MIT License にて提供しています。  
詳しくは LICENSE をご覧ください。

### Version
3.0.0

### Pull request に関して
現在 Contributor License Agreement（CLA）を準備しています。  
CLA に同意していただくまでは Pull request しても必ず受け付けられるわけではありません。   
準備が整うまでお待ちください。

