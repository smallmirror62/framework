##加密与解密（Encryption/Decryption）
###安装
```json
"require" : {
    "php" : ">=5.4.0"
    "leaps/crypt": "1.0.0",
}
```
###基本使用

这个组件极易使用：

```php
<?php
//Create an instance
$crypt = new Leaps\Crypt\Crypt();
$key = 'le password';
$text = 'This is a secret text';
$encrypted = $crypt->encrypt($text, $key);
echo $crypt->decrypt($encrypted, $key);
```

也可以使用同一实例加密多次：

```php
<?php
//Create an instance
//创建实例
$crypt = new Leaps\Crypt\Crypt();
$texts = [
    'my-key' => 'This is a secret text',
    'other-key' => 'This is a very secret'
];
foreach ($texts as $key => $text) {
    /执行加密
    $encrypted = $crypt->encrypt($text, $key);
    //解密
    echo $crypt->decrypt($encrypted, $key);
}
```

###加密选项（Encryption Options）

下面的选项可以改变加密的行为：

|  名称 | 描述  |
| ------------ | ------------ |
|  Cipher | cipher是libmcrypt提供支持的一种加密算法。  |
|  Mode |  libmcrypt支持的加密模式 (ecb, cbc, cfb, ofb) |

例子:
```php
<?php
//创建实例
$crypt = new Leaps\Crypt\Crypt();
//Use blowfish
$crypt->setCipher('blowfish');
$key = 'le password';
$text = 'This is a secret text';
echo $crypt->encrypt($text, $key);
```

###提供 Base64（Base64 Support）
为了方便传输或显示我们可以对加密后的数据进行 base64 转码：

```php
<?php
//创建实例
$crypt = new Leaps\Crypt\Crypt();
$key = 'le password';
$text = 'This is a secret text';
$encrypt = $crypt->encryptBase64($text, $key);
echo $crypt->decryptBase64($text, $key);
```