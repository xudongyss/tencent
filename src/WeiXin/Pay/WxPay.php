<?php
/**
 * 微信支付
 */
namespace XuDongYss\Tencent\WeiXin\Pay;

class WxPay{
    /**
     * @var WxPayConfig
     */
    protected static $config;
    
    protected static $notifyData = [];
    protected static $errorMessage = '';
    
    public static function getErrorMessage() {
        return static::$errorMessage;
    }
    
    protected static function setErrorMessage($errorMessage) {
        static::$errorMessage = $errorMessage;
    }
    
    /**
     * 获取异步通知全部参数
     */
    public static function getNotifyData() {
        return static::$notifyData;
    }
    
    /**
     * 返回值处理
     */
    protected static function response($result) {
        if($result['return_code'] === 'SUCCESS') {
            if($result['result_code'] === 'SUCCESS') {
                return $result;
            }
            
            static::setErrorMessage($result['err_code_des']);
            
            return false;
        }
        static::setErrorMessage($result['return_msg']);
        
        return false;
    }
    
    /**
     * 初始化
     * @param string    $appId          
     * @param string    $merchantId     商户号
     * @param string    $key
     */
    public static function init($appId, $merchantId, $key) {
        static::$config = new WxPayConfig($appId, $merchantId, $key);
    }
    
    /**
     * Native 支付
     * 模式二
     * @param string    $out_trade_no   订单号
     * @param float     $total_fee      订单金额
     * @param string    $body           描述
     * @param string    $notifyUrl      支付回调地址
     * @param string    $attach         String(127) 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
     * @param int       $expire         交易结束时间：多久后交易结束，单位：分钟
     * @return  boolean false 出错
     */
    public static function native($outTradeNo, $totalFee, $body, $notifyUrl, $attach = '', $expire = 10) {
        $input = static::unifiedorder($outTradeNo, $totalFee, $body, $notifyUrl, $attach, $expire);
        
        $input->SetTrade_type('NATIVE');
        
        /* 商品ID, trade_type=NATIVE时，此参数必传 */
        $input->SetProduct_id('PRODUCT_ID: '.$outTradeNo);
        
        try {
            $result = WxPayApi::unifiedOrder(static::$config, $input);
            
            return static::response($result);
        }catch(\Exception $e) {
            static::setErrorMessage($e->getMessage());
            
            return false;
        }
    }
    
    /**
     * APP 支付
     * @param string    $out_trade_no   订单号
     * @param float     $total_fee      订单金额
     * @param string    $body           描述
     * @param string    $notifyUrl      支付回调地址
     * @param string    $attach         String(127) 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
     * @param int       $expire         交易结束时间：多久后交易结束，单位：分钟
     * @return  boolean false 出错
     */
    public static function app($outTradeNo, $totalFee, $body, $notifyUrl, $attach = '', $expire = 10) {
        $input = static::unifiedorder($outTradeNo, $totalFee, $body, $notifyUrl, $attach, $expire);
        
        $input->SetTrade_type('APP');
        
        try {
            $result = WxPayApi::unifiedOrder(static::$config, $input);
            
            return static::appData($result);
        }catch(\Exception $e) {
            static::setErrorMessage($e->getMessage());
            
            return false;
        }
    }
    
    protected static function appData($result) {
        if(static::response($result) === false) return false;
        
        $_data = [
            'appid'=> static::$config->GetAppId(),
            'partnerid'=> static::$config->GetMerchantId(),
            'package'=> 'Sign=WXPay',
            'noncestr'=> WxPayApi::getNonceStr(),
            'timestamp'=> time(),
            'prepayid'=> $result['prepay_id'],
        ];
        $input = new WxPayDataApp();
        $input->SetValues($_data);
        /* 签名 */
        $_data['sign'] = $input->MakeSign(static::$config, false);
        
        return $_data;
    }
    
    /**
     * h5 支付，微信外
     * @param string    $out_trade_no   订单号
     * @param float     $total_fee      订单金额
     * @param string    $body           描述
     * @param string    $notifyUrl      支付回调地址
     * @param string    $redirectUrl    1：微信支付中间页调起微信收银台后超过 5 秒，2：用户点击 取消支付 或 支付完成后点 完成 按钮
     * @param string    $attach         String(127) 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
     * @param int       $expire         交易结束时间：多久后交易结束，单位：分钟
     * @return  boolean false 出错
     */
    public static function h5($outTradeNo, $totalFee, $body, $notifyUrl, $redirectUrl = '', $attach = '', $expire = 10) {
        $input = static::unifiedorder($outTradeNo, $totalFee, $body, $notifyUrl, $attach, $expire);
        
        $input->SetTrade_type('MWEB');
        
        try {
            $result = WxPayApi::unifiedOrder(static::$config, $input);
            
            return static::h5Data($result, $redirectUrl);
        }catch(\Exception $e) {
            static::setErrorMessage($e->getMessage());
            
            return false;
        }
    }
    
    protected static function h5Data($result, $redirectUrl = '') {
        if(static::response($result) === false) return false;
        if($redirectUrl) $result['mweb_url'] .= '&redirect_url='.urlencode($redirectUrl);
        
        return $result;
    }
    
    /**
     * jsApi 支付
     * @param string    $out_trade_no   订单号
     * @param float     $total_fee      订单金额
     * @param string    $openId         用户在商户appid下的唯一标识
     * @param string    $body           描述
     * @param string    $notifyUrl      支付回调地址
     * @param string    $redirectUrl    1：微信支付中间页调起微信收银台后超过 5 秒，2：用户点击 取消支付 或 支付完成后点 完成 按钮
     * @param string    $attach         String(127) 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
     * @param int       $expire         交易结束时间：多久后交易结束，单位：分钟
     * @return  boolean false 出错
     */
    public function jsApi($outTradeNo, $totalFee, $openId, $body, $notifyUrl, $attach = '', $expire = 10) {
        $input = static::unifiedorder($outTradeNo, $totalFee, $body, $notifyUrl, $attach, $expire);
        
        $input->SetOpenid($openId);
        $input->SetTrade_type('JSAPI');
        
        try {
            $result = WxPayApi::unifiedOrder(static::$config, $input);
            
            return static::jsApiData($result);
        }catch(\Exception $e) {
            static::setErrorMessage($e->getMessage());
            
            return false;
        }
    }
    
    protected static function jsApiData($result) {
        if(static::response($result) === false) return false;
        
        $_data = [
            'appId'=> static::$config->GetAppId(),
            'nonceStr'=> WxPayApi::getNonceStr(),
            'package'=> 'prepay_id='.$result['prepay_id'],
            'timeStamp'=> time(),
        ];
        $input = new WxPayDataApp();
        $input->SetValues($_data);
        /* 签名 */
        $_data['paySign'] = $input->MakeSign(static::$config, true);
        
        return $_data;
    }
    
    /**
     * 统一下单
     * @param string    $out_trade_no   订单号
     * @param float     $total_fee      订单金额
     * @param string    $body           描述
     * @param string    $notifyUrl      支付回调地址
     * @param string    $attach         String(127) 附加数据，在查询API和支付通知中原样返回，该字段主要用于商户携带订单的自定义数据
     * @param int       $expire         交易结束时间：多久后交易结束，单位：分钟
     */
    protected static function unifiedorder($outTradeNo, $totalFee, $body, $notifyUrl, $attach = '', $expire = 10) {
        $input = new WxPayUnifiedOrder();
        /* 订单号 */
        $input->SetOut_trade_no($outTradeNo);
        /* 订单金额 */
        $total_fee = $totalFee * 100;
        $input->SetTotal_fee($total_fee);
        /* 商品描述 */
        $input->SetBody($body);
        /* 支付回调地址 */
        $input->SetNotify_url($notifyUrl);
        
        /* 可选数据 */
        /* 附加数据 */
        if($attach) $input->SetAttach($attach);
        /* 交易起始时间 */
        $input->SetTime_start(date('YmdHis'));
        /* 交易结束时间 */
        if($expire > 110) $expire = 110;
        $input->SetTime_expire(date('YmdHis', time() + 60 * $expire));
        
        return $input;
    }
    
    /**
     * 异步通知校验
     */
    public static function verifyNotify() {
        $msg = '';
        $result = WxpayApi::notify(static::$config, [__NAMESPACE__.'\WxPay', 'notifyCallBack'], $msg);
        if($result === false) {
            if($msg) static::setErrorMessage($msg);
            
            return false;
        }
        
        return static::notifyData(static::$notifyData);
    }
    
    /**
     * 异步数据对象
     * @param object $objData   WxPayNotifyResults
     */
    public static function notifyCallBack($objData) {
        $_data = $objData->GetValues();
        
        /* 1、进行参数校验 */
        if(!array_key_exists('return_code', $_data) || $_data['return_code'] != 'SUCCESS') {
            static::setErrorMessage('非成功状态码');            
            
            return false;
        }
        /* 2、检查交易号 */
        if(!array_key_exists('transaction_id', $_data)){
            static::setErrorMessage('输入参数不正确');
            
            return false;
        }
        /* 3、验签 */
        try {
            $checkResult = $objData->CheckSign(static::$config);
            
            if($checkResult == false) {
                static::setErrorMessage('签名错误');
                
                return false;
            }
        }catch(\Exception $e) {
            static::setErrorMessage($e->getMessage());
            
            return false;
        }
        /* 4、查询订单 */
        $transaction_id = $_data['transaction_id'];
        $result = static::queryOrder($transaction_id);
        if($result === false) {
            static::setErrorMessage('支付未成功');
        }
        
        static::$notifyData = $_data;
        
        return true;
    }
    
    /**
     * 查询订单
     * @param string    $transaction_id 交易号
     * @return boolean
     */
    public static function queryOrder($transaction_id) {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery(static::$config, $input);
        if(isset($result['return_code']) && isset($result['result_code']) && $result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            return true;
        }
        
        return false;
    }
    
    /**
     * 异步回调回复
     * @param bool      $boolean    true：成功回复，false：失败回复
     * @param string    $msg
     */
    public static function notifyReply($boolean = true, $msg = '') {
        $input = new WxPayNotifyReply();
           
        if($boolean === true) {
            $input->SetReturn_code('SUCCESS');
            $input->SetReturn_msg('OK');
            $input->SetSign(static::$config);
            
            $xml = $input->ToXml();
            WxpayApi::replyNotify($xml);
        }else {
            $input->SetReturn_code('FAIL');
            $input->SetReturn_msg($msg);
            
            $xml = $input->ToXml();
            WxpayApi::replyNotify($xml);
        }
    }
    
    /**
     * 异步通知参数转化
     */
    protected static function notifyData($notifyData) {
        $_data = [
            'order_no'=> $notifyData['out_trade_no'],
            'total_amount'=> $notifyData['total_fee'],
            'pay_no'=> $notifyData['transaction_id'],
            'pay_time'=> date('Y-m-d H:i:s', strtotime($notifyData['time_end'])),
        ];
        
        return $_data;
    }
}