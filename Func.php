<?php
class Func
{
	//	WeChat little game encryption instance
	public static function wxGameEncrypt($info)
	{
		$raw			=	json_encode($info);
		$iv				=	openssl_random_pseudo_bytes(16);
		$session_key	=	openssl_random_pseudo_bytes(16);
		$encrypted_data	=	openssl_encrypt($raw,"AES-128-CBC", $session_key, OPENSSL_RAW_DATA, $iv);
		
		$iv				=	base64_encode($iv);
		$session_key	=	base64_encode($session_key);
		$encrypted_data	=	base64_encode($encrypted_data);
		return [
			'iv'				=>	$iv,
			'session_key'		=>	$session_key,
			'encrypted_data'	=>	$encrypted_data,
		];
	}
	
	// WeChat little game decryption instance
	public static function wxGameDecode($iv,$encrypted_data,$session_key)
	{
		$aesIV			=	base64_decode($iv);
		$aesCipher		=	base64_decode($encrypted_data);
		$aesKey			=	base64_decode($session_key);
		$result     	=   openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
		return $result;
	}
	
	// FaceBook little game encryption instance
	public static function fbGameEncrypt($arr,$secret)
	{
		$jsonStr	=	json_encode($arr);
		$f2			=	base64_encode($jsonStr);
		$f1			=	hash_hmac('sha256',$f2,$secret,true);
		$f1			=	base64_encode($f1);
		$f1			=	str_replace('+','-',$f1);
		$f1			=	str_replace('/','_',$f1);
		$f1			=	substr($f1,0,strlen($f1)-1);
		$sign		=	$f1.'.'.$f2;
		return $sign;
	}
	
	// FaceBook little game decryption instance
	public static function fbGameDecode($secret,$signature)
	{
        $sign			=	explode(".",$signature);
        if(count($sign)!==2)
        {
            return false;
        }
        $firstSign		=	$sign[0];
        $twoSign		=	$sign[1];
        $firstSign		=	str_replace('-','+',$firstSign);
        $firstSign		=	str_replace('_','/',$firstSign);
        $validateStr	= 	base64_encode(hash_hmac('sha256', $twoSign,$secret,true));
        $validateStr 	= 	substr($validateStr,0,strlen($validateStr)-1);
		return $firstSign===$validateStr ? true : false;
	}
	
	public static function callApi($url,$cookie,$data)
	{
		$ch=curl_init();
		curl_setopt_array($ch,[
			CURLOPT_RETURNTRANSFER	=>	true,
			CURLOPT_COOKIE			=>	$cookie,
			CURLOPT_URL				=>	$url,
			CURLOPT_POST			=>	true,
			CURLOPT_POSTFIELDS		=>  http_build_query($data),
			CURLOPT_CONNECTTIMEOUT	=>	60,
			CURLOPT_SSL_VERIFYPEER	=>	false
			//CURLOPT_SSL_VERIFYPEER	=>	true,
			//CURLOPT_SSL_VERIFYHOST	=>	true,
		]);
		$res=curl_exec($ch);
		curl_close($ch);
		return $res;
	}
}
