<?php
class neurologUser extends we_customer_customer {
	
	protected $Userhash;
	
	public function __construct(){
		parent::__construct();
	}
	
	public function initUserByHash($requestToken){
		if($webUserID = $this->verifyUserByHash($requestToken)){
			$this->ID = $webUserID;
			$this->load($webUserID);
		}
	}
	
	public function verifyUserByHash($requestToken){
		$weDB = isset($GLOBALS['DB_WE']) ? $GLOBALS['DB_WE'] : new DB_WE();
		if($webUserID = f('SELECT ID FROM ' . CUSTOMER_TABLE . ' WHERE Password!="" AND LoginDenied=0 AND App_UserIDHash="' . $weDB->escape($requestToken) . '"', 'ID', $weDB)){
			return $webUserID;
		}
		logApiRequests('endpoint: '.$this->request['request'].'; No User found for UserIDHash : '.$requestToken.'; apiKey : '.(array_key_exists('apiKey', $this->request) ? $this->request['apiKey'] : 'No API key given').'; App version : '.(array_key_exists('appVersion', $this->request) ? $this->request['appVersion'] : 'No App version given').'; App platform : '.(array_key_exists('appPlatform', $this->request) ? $this->request['appPlatform'] : 'No App plattform given'));
		return false;
	}
	
	public function login($username,$password){
		if(
			intval(f('SELECT COUNT(1) FROM ' . FAILED_LOGINS_TABLE . ' WHERE UserTable="tblWebUser" AND Username="' . $GLOBALS['DB_WE']->escape($username) . '" AND isValid="true" AND LoginDate >DATE_SUB(NOW(), INTERVAL ' . intval(SECURITY_LIMIT_CUSTOMER_NAME_HOURS) . ' hour)')) >= intval(SECURITY_LIMIT_CUSTOMER_NAME) ||
			intval(f('SELECT COUNT(1) FROM ' . FAILED_LOGINS_TABLE . ' WHERE UserTable="tblWebUser" AND IP="' . $_SERVER['REMOTE_ADDR'] . '" AND LoginDate >DATE_SUB(NOW(), INTERVAL ' . intval(SECURITY_LIMIT_CUSTOMER_IP_HOURS) . ' hour)')) >= intval(SECURITY_LIMIT_CUSTOMER_IP)
		){
			logApiRequests('endpoint: '.$this->request['request'].'; Login denied for User : '.$username.'; apiKey : '.(array_key_exists('apiKey', $this->request) ? $this->request['apiKey'] : 'No API key given').'; App version : '.(array_key_exists('appVersion', $this->request) ? $this->request['appVersion'] : 'No App version given').'; App platform : '.(array_key_exists('appPlatform', $this->request) ? $this->request['appPlatform'] : 'No App plattform given'));
			throw new Exception('Login denied for user');
		}
		
		$u = getHash('SELECT * FROM ' . CUSTOMER_TABLE . ' WHERE Password!="" AND LoginDenied=0 AND Username="' . $GLOBALS['DB_WE']->escape($username) . '"', null, MYSQL_ASSOC);
		if(empty($u)){
			logApiRequests('endpoint: '.$this->request['request'].'; Invalid User : '.$username.'; apiKey : '.(array_key_exists('apiKey', $this->request) ? $this->request['apiKey'] : 'No API key given').'; App version : '.(array_key_exists('appVersion', $this->request) ? $this->request['appVersion'] : 'No App version given').'; App platform : '.(array_key_exists('appPlatform', $this->request) ? $this->request['appPlatform'] : 'No App plattform given'));
			throw new Exception('Invalid User');
		}
		return ($u && we_customer_customer::comparePassword($u['Password'], $password)) ? array('UserIDHash' =>$u['App_UserIDHash']) : array('UserIDHash' => false);
	}
	
	public function resetPassword($username){
		if(empty($username)){return false;}
		$_REQUEST['s']['Username'] = $username;
		
		we_tag('customerResetPassword',array('type'=>'email','required'=>'Username','loadFields'=>'Forename,Surname,Kontakt_EMail','customerEmailField'=>'Kontakt_EMail'));
		we_tag('sessionField',array('type'=>'print','name'=>'Kontakt_EMail','to'=>'global','nameto'=>'recipientEmail'));
		if(!empty($GLOBALS['recipientEmail'])){ // we only try to send an email, when having an email address
			we_tag('sendMail',array('id'=>182,'subject'=>'NEUROLOG: Neues Passwort anfordern','recipient'=> $GLOBALS['recipientEmail'],'from'=>'info@neurolog.de','charset'=>'utf-8'));
		}
		
		return (empty($GLOBALS['ERROR']['customerResetPassword']) ? array('resetPassword' => true) : array('resetPassword' => false));
	}
}
?>