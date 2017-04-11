<?php
namespace think\oauth;
use think\Cache;
use think\Session;

use think\oauth\oauth\Exception as E;

class Qrconnect{
	protected $openids = [];
	private function isLogin(){
		if(!Session::has('qr_login') || $this->isOut()){
			return false;
		}
		return Session::get('qr_login');
	}
	public function getCode($appid,$appsecret,$redirect_uri){
		$wx = new \think\oauth\Oauth($appid,$appsecret);
    	// $url = url('admin/Test/wxInfo');
    	
    	$wx->getCode($redirect_uri);
	}
	public function getUser($appid,$appsecret,$code){
    	$wx = new \think\oauth\Oauth($appid,$appsecret);
    	$info = $wx->getUserInfo($code);
    	$res = $this->allowUser($info['openid']);
    	if(!$res){
    		exit('您没有权限登录');
    	}
    	$info['login_time'] = time();
    	// $info = json_encode($info);
    	Session::set('qr_login',$info);
	}
	protected function isOut(){
        //登录是否过期
        $login_time = Session::get('qr_login.login_time');
        if (time() - $login_time > 60) {
            Session::clear();
            return true;
        }
        return false;
	}
	public function setCache(){
		return $this->isLogin();
	}
	public function pull(){
		$res = $this->setCache();
		Session::set('qr_login',null);
		return $res;
	}
	public function setAllowUser($openids){
		$this->openids = $openids;
	}
	public function getAllowUser(){
		if(!is_array($this->openids) || empty($this->openids)){
			throw new E("请配置openids");			
		}
		return $this->openids;
	}
	private function allowUser($openid){
		$openids = $this->getAllowUser();
		if(!in_array($openid, $openids)){
			return false;
		}
		return true;
	}
	// public function getCache(){

	// }

}