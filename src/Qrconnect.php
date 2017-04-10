<?php
namespace think\oauth;
use think\Cache;
use think\Session;

use think\oauth\oauth\Exception as E;

class Qrconnect{
	private function isLogin(){
		if(!Session::has('qr_login')){
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
    	$info = json_encode($info);
    	Session::set('qr_login',$info,10);
	}
	public function setCache(){
		return $this->isLogin();
	}

}