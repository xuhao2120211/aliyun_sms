<?php

namespace lepu\aliyun\core\sdk\Auth;

interface ISigner
{
	public function  getSignatureMethod();
	
	public function  getSignatureVersion();
	
	public function signString($source, $accessSecret); 
}