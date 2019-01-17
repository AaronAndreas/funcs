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
	
	// Call interface
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
	
	// Get the current number of subtle string format
	public static function getCurTime()
	{
		$curTimes	=	explode(' ',microtime());
		$curTime	=	bcadd($curTimes[0],$curTimes[1],8);
		return $curTime;
	}
	
	// Get a readable JSON string
	public static function formatJson(Array $arr)
	{
		// 对数组中每个元素递归进行urlencode操作，保护中文字符  
		array_walk_recursive($arr, function(&$val)
		{
			if($val!==true && $val!==false && $val!==null)
			{  
				$val = urlencode($val);  
			}  
		});  
	  
		// json encode  
		$data = json_encode($arr);  
	  
		// 将urlencode的内容进行urldecode  
		$data = urldecode($data);  
	  
		// 缩进处理  
		$ret = '';  
		$pos = 0;  
		$length = strlen($data);  
		$indent = isset($indent)? $indent : '    ';  
		$newline = "\n";  
		$prevchar = '';  
		$outofquotes = true;  
	  
		for($i=0; $i<=$length; $i++){  
	  
			$char = substr($data, $i, 1);  
	  
			if($char=='"' && $prevchar!='\\'){  
				$outofquotes = !$outofquotes;  
			}elseif(($char=='}' || $char==']') && $outofquotes){  
				$ret .= $newline;  
				$pos --;  
				for($j=0; $j<$pos; $j++){  
					$ret .= $indent;  
				}  
			}  
	  
			$ret .= $char;  
			  
			if(($char==',' || $char=='{' || $char=='[') && $outofquotes){  
				$ret .= $newline;  
				if($char=='{' || $char=='['){  
					$pos ++;  
				}  
	  
				for($j=0; $j<$pos; $j++){  
					$ret .= $indent;  
				}  
			}  
	  
			$prevchar = $char;  
		}  
	  
		return $ret;  
	}
	
	// Get the PHP structure of the array
	public static function formatPhpArr($arr,$count=0)
	{
		$space		=	str_repeat('	',$count);
		$nextSpace	=	str_repeat('	',$count+1);
		$str='['.PHP_EOL;
		
		foreach($arr as $k=>$v)
		{
			if(is_object($v))
			{
				throw new \Exception('the node can\'t for the object');
			}
			if(is_resource($v))
			{
				throw new \Exception('the node can\'t for the resource');
			}
			
			if(is_array($v))
			{
				$str.=$nextSpace.'"'.$k.'"=>'.self::formatPhpArr($v,$count+1);
			}else if(is_string($v)){
				$str.=$nextSpace.'"'.$k.'"=>"'.$v.'",'.PHP_EOL;
			}else{
				$str.=$nextSpace.'"'.$k.'"=>'.$v.','.PHP_EOL;
			}
		}
		$endSymbol	=	$count==0 ? ';' : ',';
		$str.=$space.']'.$endSymbol.PHP_EOL;
		return $str;
	}
}
