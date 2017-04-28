<?php
namespace king192\oauth;

use king192\oauth\oauth\Exception as E;

class Oauth {
	protected $appid = null;
	protected $appsecret = null;
	public function __construct($wx_appid,$wx_appsecret){
		$this->appid = $wx_appid;
		$this->appsecret = $wx_appsecret;
		if(empty($this->appid)){
			throw new E('weixin appid need config'); 
		}
		if(empty($this->appsecret)){
			throw new E('weixin appsecret need config');
		}
	}
	public function getCode($redirect_uri){

		$appid = $this->appid;//'wx3a5aac7161b28013';  //公众号的唯一标识
		// $redirect_uri = urlencode("http://wzwh.suoga.org/wx/wxLogin.php".$param);
		// 注意 URL 一定要动态获取，不能 hardcode.
	    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	    // $Notify_url = "$protocol$_SERVER[HTTP_HOST]".$callcall_uri;
		$redirect_uri = urlencode($redirect_uri);
		$state = isset($_GET['url'])?$_GET['url']:'';
		// $is_scope = max(0,$_GET['is_scope']);

		$url = 'https://open.weixin.qq.com/connect/oauth2/authorize';

		// if($is_scope>0){
		if(!isset($_GET['is_scope'])){
		    // 一般模式
		    $url .= '?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state='.$state.'#wechat_redirect';
		}else{
		    // 静默模式
		    // echo 'hello world';
		    // sleep(5);
		    $url .= '?appid='.$appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_base&state='.$state.'#wechat_redirect';
		}
		header("Location:".$url);
		exit;
	}

	/**
	 * 根据code获取openid ，再获取用户信息
	 * 获取微信用户信息
	 */
	public function getUserInfo($code){
        $appid = $this->appid;//"wx3a5aac7161b28013"; //公众号的唯一标识
        $secret = $this->appsecret;//C('APPSECRET');//"d4624c36b6795d1d99dcf0547af5443d";  //公众号的appsecret
	        // trace($appid.'/////'.$secret,'=====================access_token==========================','DEBUG');
        // $code = $_GET["code"];  //第一步获取的code参数

        if(true){
	        //获取授权token
	        $get_token_url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$secret.'&code='.$code.'&grant_type=authorization_code';
	        // trace($get_token_url,'=====================get_token_url==========================','DEBUG');
	        $json_obj = json_decode($this->httpGet($get_token_url),true);
	        // trace($json_obj,'=====================wx_debug==========================','DEBUG');
	        // var_dump($json_obj);
	        $access_token = $json_obj['access_token'];
	        $openid = $json_obj['openid'];
	        // var_dump($json_obj);
	        //根据openid和access_token查询用户信息
	        $get_user_info_url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
	        $res = $this->httpGet($get_user_info_url);
	        $user_obj = json_decode($res,true);
	        $user_obj['access_token'] = $access_token;
            // echo '=====================<br>';
            if(isset($_GET['debug'])){
	        	var_dump($user_obj);
	        	exit;
            }
            return $user_obj;
	    }else{

            $get_tken_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
            $get_tken = json_decode($this->httpGet($get_tken_url),true);
            // var_dump($get_tken);
// trace($get_tken,'=====================wx_debug1==========================','DEBUG');
            $access_token = $get_tken['access_token'];
            $get_subscribe_url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$puid_openid.'&lang=zh_CN';
            $get_subscribe = json_decode($this->httpGet($get_subscribe_url),true);
            $get_subscribe['access_token'] = $access_token;
            if(isset($_GET['debug'])){
            	var_dump($get_subscribe);
            	exit;
            }
            return $get_subscribe;
	    }
	}

    private function httpGet($url) {
	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
	    // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
	    // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
	    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
	    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
	    curl_setopt($curl, CURLOPT_URL, $url);

	    $res = curl_exec($curl);
        if(false === $res){
        	throw new E('curl error:'.curl_error($curl));
        }
	    curl_close($curl);

	    return $res;
	  }
    protected function https_request($url,$data = null){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        if(false === $output){
        	throw new E('curl error:'.curl_error($curl));
        }
        curl_close($curl);
        return $output;
    }
	/**
	 * 发起POST请求
	 *
	 * @access public
	 * @param string $url
	 * @param array $data
	 * @return string
	 */
	protected function post($url, $data = '', $cookie = '', $type = 0)
	{
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 对认证证书来源的检查  
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 从证书中检查SSL加密算法是否存在
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    if($cookie){
	        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	        curl_setopt ($ch, CURLOPT_REFERER,'https://wx.qq.com');
	    }
	    if($type){
	        $header = array(
	        'Content-Type: application/json',
	        );
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	    }

	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	    curl_setopt($ch, CURLOPT_SAFE_UPLOAD, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
	    $output = curl_exec($ch);
	    curl_close($ch);
	    return $output;
	}
}