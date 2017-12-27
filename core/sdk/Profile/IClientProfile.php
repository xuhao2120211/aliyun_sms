<?php

namespace lepu\aliyun\core\sdk\Profile;

interface IClientProfile
{
	public function getSigner();
	
	public function getRegionId();
	
	public function getFormat();
	
	public function getCredential();
}