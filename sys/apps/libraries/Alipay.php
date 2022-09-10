<?php
if (!defined('FCPATH')) exit('No direct script access allowed');
/*
'软件名称：漫城CMS（Mccms）
'官方网站：http://www.mccms.cn/
'软件作者：桂林崇胜网络科技有限公司（By:烟雨江南）
'--------------------------------------------------------
'Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
'遵循Apache2开源协议发布，并提供免费使用。
'--------------------------------------------------------
*/
class Alipay {

    public function __construct (){
		//商户ID
		$this->partner = Pay_Ali_ID;
		//商户MD5密钥
		$this->SKey = Pay_Ali_Key;
		//同步地址
		$this->return_url = 'http://'.Web_Url.Web_Path.'index.php/api/pay/return_url';
		//异步地址
		$this->notify_url = 'http://'.Web_Url.Web_Path.'index.php/api/pay/notify_url/alipay';
	}

	//快捷支付
	public function qrcode($dingdan,$total_fee,$body=''){
		$params = array(
			"service"       => 'create_direct_pay_by_user',
			"partner"       => $this->partner,
			"seller_id"     => $this->partner,
			"payment_type"	=> 1,
			"notify_url"	=> $this->notify_url,
			"return_url"	=> $this->return_url.'/'.$dingdan,
			"out_trade_no"	=> $dingdan,
			"subject"	=> $body,
			"total_fee"	=> $total_fee,
			"body"	=> $body,
			"_input_charset" => 'utf-8'
		);
		$params['sign'] = $this->md5_sign($params);
		$params['sign_type'] = 'MD5';

		$url = 'https://mapi.alipay.com/gateway.do?';
		foreach ($params as $k => $v) {
			$url .= $k.'='.urlencode($v).'&';
		}
		return substr($url,0,-1);
	}

	//H5支付
	public function h5($dingdan,$total_fee,$body = ''){
		//return $this->qrcode($dingdan,$total_fee,$body);
		$params = array(
			"service"       => 'alipay.wap.create.direct.pay.by.user',
			"partner"       => $this->partner,
			"seller_id"     => $this->partner,
			"payment_type"	=> 1,
			"notify_url"	=> $this->notify_url,
			"return_url"	=> $this->return_url,
			"out_trade_no"	=> $dingdan,
			"subject"	=> $body,
			"total_fee"	=> $total_fee,
			"body"	=> $body,
			"show_url" => 'http://'.Web_Url.Web_Path,
			"_input_charset" => 'utf-8'
		);
		$params['sign'] = $this->md5_sign($params);
		$params['sign_type'] = 'MD5';

		$url = 'https://mapi.alipay.com/gateway.do?';
		foreach ($params as $k => $v) {
			$url .= $k.'='.urlencode($v).'&';
		}
		return substr($url,0,-1);
	}

	//生成MD5签名
	public function md5_sign($para){
		$para_filter = array();
		foreach($para as $key=>$val){
			if($key == "sign" || $key == "sign_type" || $val == "") continue;
			$para_filter[$key] = $para[$key];
		}
		ksort($para_filter);
		reset($para_filter);
		$prestr = $this->createLinkstring($para_filter).$this->SKey;
		return md5($prestr);
	}

	//验证签名
	public function is_sign(){
		//数组转字符串
		$para = isset($_POST['sign']) ? $_POST : $_GET;
		$sign = $para['sign'];
		$mysgin = $this->md5_sign($para);
		if($mysgin == $sign) {
			if($para['trade_status'] == 'TRADE_SUCCESS'){
				return $para['out_trade_no'];
			}
		}
		return false;
	}

	//签名转换
	function createLinkstring($para) {
		$arg  = "";
		while (list ($key, $val) = each ($para)) {
			$arg.=$key."=".$val."&";
		}
		//去掉最后一个&字符
		$arg = substr($arg,0,count($arg)-2);
		//如果存在转义字符，那么去掉转义
		if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
		return $arg;
	}
}