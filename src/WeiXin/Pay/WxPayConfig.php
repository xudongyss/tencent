<?php
/**
 * 微信支付配置
 */
namespace XuDongYss\Tencent\WeiXin\Pay;

class WxPayConfig extends WxPayConfigInterface {
    /* AppId */
    protected $appId = '';
    /* 商户号 */
    protected $merchantId = '';
    /* API 加密 KEY */
    protected $key = '';
    /* AppSecret */
    protected $appSecret = '';

    protected $notifyUrl = '';
    protected $signType = 'MD5';
    
    public function __construct($appId, $merchantId, $key, $appSecret = '') {
        $this->appId = $appId;
        $this->merchantId = $merchantId;
        $this->key = $key;
        if($appSecret) $this->appSecret = $appSecret;
    }
    
    /**
     * 设置 APPID
     */
    public function setAppId($appId) {
        $this->appId = $appId;
        
        return $this;
    }
    
    /**
     * 设置 商户号
     */
    public function setMerchantId($merchantId) {
        $this->merchantId = $merchantId;
        
        return $this;
    }
    
    /**
     * 设置 KEY：商户支付密钥
     */
    public function setKey($key) {
        $this->key = $key;
        
        return $this;
    }
    
    /**
     * 设置 支付回调url
     */
    public function setNotifyUrl($notifyUrl) {
        $this->notifyUrl = $notifyUrl;
        
        return $this;
    }
    
    /**
     * 设置 公众帐号secert（仅JSAPI支付的时候需要配置）
     */
    public function setAppSecret($appSecret) {
        $this->appSecret = $appSecret;
        
        return $this;
    }
    
    public function setSignType($signType) {
        $this->signType = $signType;
        
        return $this;
    }
    
    /**
     * 微信公众号信息配置
     * APPID：绑定支付的APPID
     */
	public function GetAppId() {
	    return $this->appId;
	}
	
	/**
	 * MCHID：商户号
	 */
	public function GetMerchantId() {
	    return $this->merchantId;
	}
	
	/**
	 * 支付回调url
	 */
	public function GetNotifyUrl() {
		return $this->notifyUrl;
	}
	
	/**
	 * 签名和验证签名方式， 支持md5和sha256方式
	 * 默认：md5
	 */
	public function GetSignType() {
		return $this->signType;
	}

	/**
	 * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
	 * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
	 * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
	 */
	public function GetProxy(&$proxyHost, &$proxyPort) {
		$proxyHost = "0.0.0.0";
		$proxyPort = 0;
	}
	
	/**
	 * TODO：接口调用上报等级，默认紧错误上报（注意：上报超时间为【1s】，上报无论成败【永不抛出异常】，
	 * 不会影响接口调用流程），开启上报之后，方便微信监控请求调用的质量，建议至少
	 * 开启错误上报。
	 * 上报等级，0.关闭上报; 1.仅错误出错上报; 2.全量上报
	 * @var int
	 */
	public function GetReportLevenl() {
		return 1;
	}

	/**
	 * KEY：商户支付密钥
	 */
	public function GetKey() {
		return $this->key;
	}
	
	/**
	 * APPSECRET：公众帐号secert（仅JSAPI支付的时候需要配置）
	 */
	public function GetAppSecret() {
		return $this->appSecret;
	}

	/**
	 * TODO：设置商户证书路径
	 * 证书路径, 注意应该填写绝对路径（仅退款、撤销订单时需要
	 */
	public function GetSSLCertPath(&$sslCertPath, &$sslKeyPath) {
		$sslCertPath = '../cert/apiclient_cert.pem';
		$sslKeyPath = '../cert/apiclient_key.pem';
	}
}