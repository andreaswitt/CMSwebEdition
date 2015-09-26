<?php
class apiKey  {
	
	var $allowedApiKeys = array('nWF1nsP6YPPopn');
	
	public function verifyKey($apiKey){
		if(!in_array($apiKey, $this->allowedApiKeys)){
			logApiRequests('endpoint: '.$this->request['request'].'; Api Key not allowed; apiKey : '.(array_key_exists('apiKey', $this->request) ? $this->request['apiKey'] : 'No API key given').'; App version : '.(array_key_exists('appVersion', $this->request) ? $this->request['appVersion'] : 'No App version given').'; App platform : '.(array_key_exists('appPlatform', $this->request) ? $this->request['appPlatform'] : 'No App plattform given'));
			return false;
		}
		return true;
	}
}
?>