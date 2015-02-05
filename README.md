YConnect PHP SDK
=======
YConnectのAuthorization Codeフローを実装するためのライブラリーです。  
実装に必要なクラスファイルが定義されています。 

### 使用API
* YConnect
  * http://developer.yahoo.co.jp/yconnect/server_app/explicit/
* UserInfo API
  * http://developer.yahoo.co.jp/yconnect/userinfo.html
* BillingAddress API
  * http://fastpay.yahooapis.jp/v1/address

### 構成環境
PHP 5.3 （5.3.x）以降（curl、json関連のパッケージ必須）

### 利用方法
libディレクトリをinclude_pathに設定してrequireあるいはincludeしてご利用ください。

### BillingAddress APIの利用方法
BillingAddress APIの利用には、Yahoo!ウォレット FastPay( https://fastpay.yahoo.co.jp )の登録が必要です。
利用までのフローを記載したページを用意しましたので、以下を参照してください。
https://fastpay.yahoo.co.jp/docs/guide_address

### License
本ライブラリおよびサンプルコード等は MIT License にて提供しています。  
詳しくは LICENSE をご覧ください。

### Version
2.1.0

### Pull request に関して
現在 Contributor License Agreement（CLA）を準備しています。  
CLA に同意していただくまでは Pull request しても必ず受け付けられるわけではありません。   
準備が整うまでお待ちください。

