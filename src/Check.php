<?php
namespace king192\wechat_oauth;
use king192\wechat_oauth\oauth\Exception as E;
/**
 * 微信接入类（token认证）
 */
class Check {
	protected $wx_token_oauth = null;
	public function __construct($token){
		$this->wx_token_oauth = $token;
        if (empty($this->wx_token_oauth)) {
            throw new E('TOKEN is not defined!');
        }		
	}
	public function check(){
		if($this->checkSignature()){
			exit($_GET['echostr']);
		}
		//校验失败，不做处理
	}
	/**
	 * 校验是否合法
	 * @return bool [description]
	 */
    private function checkSignature(){
        // you must define TOKEN by yourself
        $token = $this->wx_token_oauth;
        
        $signature = isset($_GET["signature"])?$_GET["signature"]:'';
        $timestamp = isset($_GET["timestamp"])?$_GET["timestamp"]:'';
        $nonce = isset($_GET["nonce"])?$_GET["nonce"]:'';
                
        // $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        // use SORT_STRING rule
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}