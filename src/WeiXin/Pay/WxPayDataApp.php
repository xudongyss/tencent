<?php
namespace XuDongYss\Tencent\WeiXin\Pay;

class WxPayDataApp extends WxPayDataBaseSignMd5{
    public function SetValues($values) {
        $this->values = $values;
        
        return $this;
    }
}