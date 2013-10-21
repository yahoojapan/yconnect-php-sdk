YConnect PHP SDK
=======
YConnectのAuthorization Codeフローを実装するためのライブラリーです。  
実装に必要なクラスファイルが定義されています。 

### 使用API
* YConnect
  * http://developer.yahoo.co.jp/yconnect/server_app/explicit/
* UserInfo API
  * http://developer.yahoo.co.jp/yconnect/userinfo.html

### 構成環境
PHP 5.2 （5.2.x）以降（curl、json関連のパッケージ必須）

### 利用方法
本SDKを利用するためには以下のライブラリーが必要です。
* JWT.php（JSON Web Token）
  * https://github.com/luciferous/jwt

ダウンロードしたJWT.phpをYConnect SDKのlibディレクトリ下に配置してください。  
YConnect.incにはYConnectを利用するために必要なクラスが定義されています。  
libディレクトリをinclude_pathに設定してrequireあるいはincludeしてご利用ください。

### License
本ライブラリおよびサンプルコード等は MIT License にて提供しています。  
詳しくは LICENSE をご覧ください。

### Pull request に関して
現在 Contributor License Agreement（CLA）を準備しています。  
CLA に同意していただくまでは Pull request しても必ず受け付けられるわけではありません。   
準備が整うまでお待ちください。

