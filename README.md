# 腾讯系 SDK

[TOC]

## 安装

```php
composer require xudongyss/tencent
```

## 快速使用

### 微信支付

#### 初始化

```php
require_once 'vendor/autoload.php';

use tencent\weixin\pay\WxPay;

$appId = '';
/* 商户号 */
$merchantId = '';
/* API KEY */
$key = '';
/* 初始化 */
WxPay::init($appId, $merchantId, $key);
```

#### native 支付，模式二，适用于：PC 网站

php 生成二维码：https://github.com/xudongyss/phpqrcode

```php
/* 订单号 */
$outTradeNo = '202007151619';
/* 订单金额 */
$totalFee = 1;
/* 描述 */
$body = '微信支付';
/* 异步回调 url */
$notifyUrl = '';
/* 可选值: 默认 '', String(127) 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据*/
$attach = '';
/* 可选值: 支付超时时间, 最大值: 100, 单位: 分钟, 默认值: 10, 10 分钟后 */
$expire = 10;
WxPay::native($outTradeNo, $totalFee, $body, $notifyUrl, $attach, $expire);

//返回值: 
//异常返回: false, 使用 WxPay::getErrorMessage() 获取错误描述
//正常返回
Array
(
    [appid] => 
    [code_url] => 	//用户生成支付二维码
    [mch_id] => 1387697402
    [nonce_str] => wo1hGF5GqZetJoDt
    [prepay_id] => wx16113738316617a787f99be51378182900
    [result_code] => SUCCESS
    [return_code] => SUCCESS
    [return_msg] => OK
    [sign] => 
    [trade_type] => NATIVE
)
```

#### APP 支付

```php
/* 订单号 */
$outTradeNo = '202007151619';
/* 订单金额 */
$totalFee = 1;
/* 描述 */
$body = '微信支付';
/* 异步回调 url */
$notifyUrl = '';
/* 可选值: 默认 '', String(127) 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据*/
$attach = '';
/* 可选值: 支付超时时间, 最大值: 100, 单位: 分钟, 默认值: 10, 10 分钟后 */
$expire = 10;
WxPay::native($outTradeNo, $totalFee, $body, $notifyUrl, $attach, $expire);

//返回值: 
//异常返回: false, 使用 WxPay::getErrorMessage() 获取错误描述
//正常返回：APP 可根据接口文档直接使用相关数据调用支付，无需任何处理
Array
(
    [appid] => 
    [partnerid] => 
    [package] => Sign=WXPay
    [noncestr] => 1se71owcp1of6ovi7vyji7mjwbph0zq5
    [timestamp] => 1594880068
    [prepayid] => wx16141428062738d054d9ec7e1219435700
    [sign] => 
)
```

#### 小程序 支付

```php
/* 订单号 */
$outTradeNo = '202007151619';
/* 订单金额 */
$totalFee = 1;
/* 用户在商户appid下的唯一标识 */
$openId = '';
/* 描述 */
$body = '微信支付';
/* 异步回调 url */
$notifyUrl = '';
/* 可选值: 默认 '', String(127) 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据*/
$attach = '';
/* 可选值: 支付超时时间, 最大值: 100, 单位: 分钟, 默认值: 10, 10 分钟后 */
$expire = 10;
WxPay::jsApi($outTradeNo, $totalFee, $openId, $body, $notifyUrl, $attach, $expire);

//返回值: 
//异常返回: false, 使用 WxPay::getErrorMessage() 获取错误描述
//正常返回：小程序 可根据接口文档直接使用相关数据调用支付，无需任何处理
Array
(
    [appId] => 
    [timeStamp] => 
    [nonceStr] => 
    [package] => 
    [signType] => 
    [paySign] => 
)
```

#### 异步回调

回调页：需初始化

```php
$result = WxPay::verifyNotify();
//返回值：
/*
 * 异常返回：
 * 		false，使用 WxPay::getErrorMessage() 获取错误描述，
 *		调用：WxPay::notifyReply(false, WxPay::getErrorMessage())，调用后，请不要有任何输出，通知微信服务器
 * 业务处理过程中出错，或发生异常，均需调用 WxPay::notifyReply(false, WxPay::getErrorMessage())，通知微信服务器，微信服务器会在一定时间内继续回调
 */
//正常返回：原始数据：使用 WxPay::getNotifyData() 获取
Array
(
    [order_no] => 订单号
    [total_amount] => 订单金额
    [pay_no] => 支付交易号
    [pay_time] => 支付时间
)
//业务处理完成后
WxPay::notifyReply();//调用后，请不要有任何输出
```

### 微信授权登录

#### 初始化

```php
require_once 'vendor/autoload.php';

use tencent\weixin\oauth\WxOAuth;

$appId = '';
$appSecret = '';
WxOAuth::init($appId, $appSecret);
```

#### 获取 AccessToken

```php
$code = '';
$result = WxOAuth::getAccessToken($code);
//返回值: 
//异常返回: false, 使用 WxPay::getErrorMessage() 获取错误描述
//正常返回：
Array
(
    [access_token] => 
    [expires_in] => 7200
    [refresh_token] => 
    [openid] => 
    [scope] => snsapi_login
    [unionid] => 
)
```

#### 获取用户个人信息

```php
$accessToken = '';
$openId = '';
$result = WxOAuth::getUserInfo($accessToken, $openId);
//返回值: 
//异常返回: false, 使用 WxPay::getErrorMessage() 获取错误描述
//正常返回：
Array
(
    [openid] => 
    [nickname] => 
    [sex] => 
    [language] => zh_CN
    [city] => 
    [province] => 
    [country] => CN
    [headimgurl] => 
    [privilege] => Array
        (
        )

    [unionid] => 
)
```



