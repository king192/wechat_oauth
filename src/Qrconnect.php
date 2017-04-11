<?php
namespace think\oauth;
use think\Cache;
use think\Session;

use think\oauth\oauth\Exception as E;

class Qrconnect{
	protected $openids = [];
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
    	$res = $this->allowUser($info['openid'])
    	if(!$res){
    		exit('您没有权限登录');
    	}
    	// $info = json_encode($info);
    	Session::set('qr_login',$info);
	}
	public function setCache(){
		return $this->isLogin();
	}
	public function pull(){
		$res = $this->setCache();
		if($res){
			Session::set('qr_login',null);
		}
		return $res;
	}
	public function setAllowUser($openids){
		$this->openids = $openids;
	}
	public function getAllowUser(){
		if(!is_array($this->openids) || $this->openids == []){
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