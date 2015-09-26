<we:include type="template" id="37" once="true" comment="/app/api/api.class.tmpl"/>
<we:include type="template" id="41" once="true" comment="/app/api/models/user.class.tmpl"/>
<we:include type="template" id="42" once="true" comment="/app/api/models/apikey.class.tmpl"/>
<we:include type="template" id="46" once="true" comment="/app/api/models/navi.functions.tmpl"/>
<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/webEdition/we/include/we_global.inc.php');

class neurolog extends API{
	
	protected $user;
	
	public function __construct($request, $origin) {
		parent::__construct($request);
		
		// Abstracted out for example
		$APIKey = new apiKey();
		$user = new neurologUser();
		
		switch(true){
			case (!array_key_exists('apiKey', $this->request)):
				t_e('neurolog API: No API Key provided');
				throw new Exception('No API Key provided');
			case (!$APIKey->verifyKey($this->request['apiKey'], $origin)):
				t_e('neurolog API: Invalid API Key ='.$this->request['apiKey']);
				throw new Exception('Invalid API Key');
			case (array_key_exists('token', $this->request) && !$user->verifyUserByHash($this->request['token'])):
				t_e('neurolog API: Invalid User Token ='.$this->request['token']);
				throw new Exception('Invalid User Token');
			default:
				$this->user = $user;
		}
	}
	
	
	protected function user() {
		if ($this->method == 'GET') {
			
			logApiRequests('endpoint: '.$this->request['request'].'; Username : '.(!empty($this->args[0]) ? $this->args[0] : 'No username given' ).'; apiKey : '.(array_key_exists('apiKey', $this->request) ? $this->request['apiKey'] : 'No API key given').'; App version : '.(array_key_exists('appVersion', $this->request) ? $this->request['appVersion'] : 'No App version given').'; App platform : '.(array_key_exists('appPlatform', $this->request) ? $this->request['appPlatform'] : 'No App plattform given'));
			
			switch($this->verb){
				case 'login':
					return $this->user->login($this->args[0], $this->args[1]);
				case 'resetpassword':
					return $this->user->resetPassword($this->args[0]);
				default:
					throw new Exception('Request not allowed');
			}
		}
		
		return "Only accepts GET requests";
	}
	
	protected function docs() {
		if ($this->method == 'GET') {
			if(!array_key_exists('token', $this->request)){
				logApiRequests('endpoint: '.$this->request['request'].';  No UserIDHash povided; Api Key : '.(array_key_exists('apiKey', $this->request) ? $this->request['apiKey'] : 'No API key given').'; App version : '.(array_key_exists('appVersion', $this->request) ? $this->request['appVersion'] : 'No App version given').'; App platform : '.(array_key_exists('appPlatform', $this->request) ? $this->request['appPlatform'] : 'No App plattform given'));
				throw new Exception('No User Token provided');
			}
			
			$u = getHash('SELECT * FROM ' . CUSTOMER_TABLE . ' WHERE Password!="" AND LoginDenied=0 AND App_UserIDHash="' . $GLOBALS['DB_WE']->escape($this->request['token']) . '"', null, MYSQL_ASSOC);
			
			if(empty($u)){
				logApiRequests('endpoint: '.$this->request['request'].';  Token : Invalid User Token; apiKey : '.(array_key_exists('apiKey', $this->request) ? $this->request['apiKey'] : 'No API key given').'; App version : '.(array_key_exists('appVersion', $this->request) ? $this->request['appVersion'] : 'No App version given').'; App platform : '.(array_key_exists('appPlatform', $this->request) ? $this->request['appPlatform'] : 'No App plattform given'));
				throw new Exception('Invalid User Token');
			}
			
			$_SESSION['webuser'] = $u;
			$_SESSION['webuser']['registered'] = true;
			
			switch($this->verb){
				case 'list':
					we_tag('navigation',array('navigationname'=>'bookList','parentid'=>69));
					$GLOBALS['books'] = array();
					we_tag('navigationEntry',array('type'=>'folder','navigationname'=>'bookList'));
					we_tag('navigationEntry',array('type'=>'item','navigationname'=>'bookList'));
					we_tag('navigationEntry',array('type'=>'folder','navigationname'=>'bookList','level'=>1),"<?php 
								printElement(we_tag('navigationField',array('name'=>'id','to'=>'global','nameto'=>'bookID')));
								printElement(we_tag('navigationField',array('name'=>'text','to'=>'global','nameto'=>'bookName')));
								printElement(we_tag('navigationField',array('name'=>'position','to'=>'global','nameto'=>'bookPosition')));
								printElement(we_tag('navigationField',array('name'=>'href','to'=>'global','nameto'=>'bookHref')));
								printElement(we_tag('navigationField',array('name'=>'icon','to'=>'global','nameto'=>'bookCover')));
								if(!empty(\$GLOBALS['bookHref'])){
									\$GLOBALS['books'][\$GLOBALS['bookID']]['name'] = \$GLOBALS['bookName'];
									\$GLOBALS['books'][\$GLOBALS['bookID']]['position'] = \$GLOBALS['bookPosition'];
									\$GLOBALS['books'][\$GLOBALS['bookID']]['cover'] = \$GLOBALS['bookCover'];
								}
	?>");
					we_tag('navigationWrite',array('navigationname'=>'bookList'));
					
					if(count($GLOBALS['books']) < 1){
						logApiRequests('endpoint: '.$this->request['request'].';  No training material found; Token : '.(!array_key_exists('token', $this->request) ? 'No UserIDHash given' : $this->request['token']).'; apiKey : '.(array_key_exists('apiKey', $this->request) ? $this->request['apiKey'] : 'No API key given').'; App version : '.(array_key_exists('appVersion', $this->request) ? $this->request['appVersion'] : 'No App version given').'; App platform : '.(array_key_exists('appPlatform', $this->request) ? $this->request['appPlatform'] : 'No App plattform given'));
						throw new Exception('No training material found');
					}
				
					$bookList = array();
					$cnt = 0;
					foreach($GLOBALS['books'] as $bookID => $bookArray){
						$bookList[$cnt]['bookid'] = $bookID;
						$bookList[$cnt]['title'] = $bookArray['name'];
						$bookList[$cnt]['cover'] = ((isset($_SERVER['HTTP_HOST']) && !empty($bookArray['cover']) && $bookArray['cover'] != '/') ? 'http://'.$_SERVER['HTTP_HOST'].$bookArray['cover'] : '');
						$bookList[$cnt]['position'] = $bookArray['position'];
						$bookList[$cnt]['lastmodified'] = getLastModifiedTimestamp($bookID);
						$cnt ++;
					}
					
					logApiRequests('endpoint: '.$this->request['request'].'; Token : '.(!array_key_exists('token', $this->request) ? 'No UserIDHash given' : $this->request['token']).'; User : '.$_SESSION['webuser']['Username'].'; List of training material'.implode(',',$bookList).'; apiKey : '.(array_key_exists('apiKey', $this->request) ? $this->request['apiKey'] : 'No API key given').'; App version : '.(array_key_exists('appVersion', $this->request) ? $this->request['appVersion'] : 'No App version given').'; App platform : '.(array_key_exists('appPlatform', $this->request) ? $this->request['appPlatform'] : 'No App plattform given'));
				
					unset($GLOBALS['books']);
					unset($_SESSION['webuser']);
				
					return $bookList;

				/**
				* return content training material
				* @param $this->args[0] = ID of specific training material (ID from navigation modul)
				*/
				case 'get': 
				
					ini_set('memory_limit', '256M');
					if(empty($this->args[0]) || !is_numeric($this->args[0])){
						logApiRequests('endpoint: '.$this->request['request'].'; Invalid DocID; Token'.(!array_key_exists('token', $this->request) ? 'No UserIDHash given' : $this->request['token']).'; apiKey : '.(array_key_exists('apiKey', $this->request) ? $this->request['apiKey'] : 'No API key given').'; App version : '.(array_key_exists('appVersion', $this->request) ? $this->request['appVersion'] : 'No App version given').'; App platform : '.(array_key_exists('appPlatform', $this->request) ? $this->request['appPlatform'] : 'No App plattform given'));
						throw new Exception('Invalid Doc ID');
					}
					
					$this->user->initUserByHash($this->request['token']);
				
					logApiRequests('endpoint: '.$this->request['request'].'; Token : '.(!array_key_exists('token', $this->request) ? 'No UserIDHash given' : $this->request['token']).'; User : '.$this->user->Surname.'; apiKey : '.(array_key_exists('apiKey', $this->request) ? $this->request['apiKey'] : 'No API key given').'; App version : '.(array_key_exists('appVersion', $this->request) ? $this->request['appVersion'] : 'No App version given').'; App platform : '.(array_key_exists('appPlatform', $this->request) ? $this->request['appPlatform'] : 'No App plattform given'));
				
					/**
					*first, we create temp file for JSON Objet
					* TEMP_DIR = webEdition Temp-Dir
					*/
					$tempDir = is_dir($_SERVER['DOCUMENT_ROOT'].TEMP_DIR) ? $_SERVER['DOCUMENT_ROOT'].TEMP_DIR : sys_get_temp_dir();
					$tempFile = tempnam($tempDir, $this->user->Surname."_".$this->args[0]."_");//create user specific temp file for JSON Object
					
					we_tag('navigation',array('navigationname'=>'book','parentid'=>$this->args[0]));
					$GLOBALS['books'] = array();
					we_tag('navigationEntry',array('type'=>'folder','navigationname'=>'book'),"<?php
								printElement(we_tag('navigationField',array('name'=>'id','to'=>'global','nameto'=>'bookID')));
								printElement(we_tag('navigationField',array('name'=>'text','to'=>'global','nameto'=>'bookName')));
								printElement(we_tag('navigationField',array('name'=>'position','to'=>'global','nameto'=>'bookPosition')));
								printElement(we_tag('navigationField',array('name'=>'level','to'=>'global','nameto'=>'bookLevel')));
								printElement(we_tag('navigationField',array('name'=>'href','to'=>'global','nameto'=>'bookLink')));
							 
								\$GLOBALS['books'][\$GLOBALS['bookID']]['title'] = \$GLOBALS['bookName']; 
								\$GLOBALS['books'][\$GLOBALS['bookID']]['position'] = \$GLOBALS['bookPosition'];
								\$GLOBALS['books'][\$GLOBALS['bookID']]['level'] = \$GLOBALS['bookLevel'];
								\$GLOBALS['books'][\$GLOBALS['bookID']]['link'] = \$GLOBALS['bookLink'];
								
								printElement(we_tag('navigationEntries'));
					?>");
					we_tag('navigationWrite',array('navigationname'=>'book'));
					unset($GLOBALS['we_navigation']);
				
					$cnt = 0;
					
					file_put_contents($tempFile, serialize(array()));
					
					foreach($GLOBALS['books'] as $bookID => $bookArray){
						//$book[$cnt]['bookid'] = $bookID;
						$book = array();
						
						$book[$cnt]['title'] = $bookArray['title'];
						$book[$cnt]['level'] = $bookArray['level'];
						$book[$cnt]['position'] = $bookArray['position'];
						if(!empty($bookArray['link'])){
							//if hidedireindex is activated for navi tool, we have zo put 'index.php' to the url for function path_to_id
							$completeLink = $bookArray['link'].(stripos($bookArray['link'], ".php") ? '' : 'index.php');
							//mode: treat and/or learn
							$book[$cnt]['mode'] = getMode($completeLink);
							
							//the real content
							$GLOBALS['weDocumentID'] = path_to_id($completeLink);
							?><we:include type="template" id="48"/><?php
							
							$book[$cnt]['content'] = $GLOBALS['content'];
							//$book[$cnt]['content'] = storeInBuffer($bookArray['link']);
							
							//url without prefix '/app/' and file type e.g. '.php'
							$book[$cnt]['url'] = str_replace("/app/","",((strrpos($bookArray['link'],'.')) ? substr($bookArray['link'],0,strrpos($bookArray['link'],'.')) : $bookArray['link']));
						}
						$fileArray = unserialize(file_get_contents($tempFile));
						$fileArray = array_merge($fileArray,$book);
						file_put_contents($tempFile, serialize($fileArray));
						unset($fileArray);
						$cnt ++;
					}
				
					unset($GLOBALS['books']);
					unset($_SESSION['webuser']);
					unset($user);
					
					$completeBook = unserialize(file_get_contents($tempFile));
					return $completeBook;
					
					/**
					header("Cache-Control: ", true);
					header("Content-Type: application/json");
					header('Content-Type: application/force-download');
					header('Content-Length: '.filesize($tempFile));
					ob_clean();
					flush();
					readfile($tempFile);
					exit();
					*/
				default:
					unset($_SESSION['webuser']);
					return "Request not allowed";
			}
		}
		
		return "Only accepts GET requests";
	}
	
}
?>