<?php
namespace XuDongYss\Tencent\WeiXin\Oauth;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class WxOAuth{
    protected static $gatewayHost = 'https://api.weixin.qq.com';
    
    protected static $appId = '';
    protected static $appSecret = '';
    
    protected static $notifyData = [];
    protected static $errorMessage = '';
    
    public function init($appId, $appSecret) {
        static::$appId = $appId;
        static::$appSecret = $appSecret;
    }
    
    public static function getErrorMessage() {
        return static::$errorMessage;
    }
    
    protected static function setErrorMessage($errorMessage) {
        static::$errorMessage = $errorMessage;
    }
    
    /**
     * 获取 access_token
     */
    public static function getAccessToken($code) {
        $uri = '/sns/oauth2/access_token';
        
        $_data = [
            'appid'=> static::$appId,
            'secret'=> static::$appSecret,
            'code'=> $code,
            'grant_type'=> 'authorization_code',
        ];
        
        $response = static::requestGet($uri, $_data);
        if($response === false) return false;
        
        return static::response($response);
    }
    
    public static function getUserInfo($accessToken, $openId) {
        $uri = '/sns/userinfo';
        
        $_data = [
            'access_token'=> $accessToken,
            'openid'=> $openId,
        ];
        
        $response = static::requestGet($uri, $_data);
        if($response === false) return false;
        
        return static::response($response);
    }
    
    protected static function response($response) {
        $json = json_decode($response, true);
        if(isset($json['errcode'])) {
            static::setErrorMessage($json['errmsg']);
            
            return false;
        }
        
        return $json;
    }
    
    protected static function requestGet($uri, $_data) {
        $client = new Client(['base_uri'=> static::$gatewayHost]);
        try {
            $response = $client->request('GET', $uri, ['query'=> $_data]);
            
            return $response->getBody();
        }catch(RequestException $e) {
            static::setErrorMessage('请求失败');
            
            return false;
        }
    }
}