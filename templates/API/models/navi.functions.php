<?php 
function getLastModifiedTimestamp($naviDocID){
	$GLOBALS['docLinks'] = array();
	we_tag('navigation',array('navigationname'=>'book','id'=>$naviDocID));
	we_tag('navigationEntry',array('type'=>'folder','navigationname'=>'book'),"<?php
						printElement(we_tag('navigationField',array('name'=>'href','to'=>'global','nameto'=>'docLink')));
						if(!empty(\$GLOBALS['docLink'])){
							\$GLOBALS['docLinks'][] = \$GLOBALS['docLink'];
						}
						printElement(we_tag('navigationEntries'));
	?>");
	we_tag('navigationWrite',array('navigationname'=>'book'));
	$lastModified = 0;
	foreach($GLOBALS['docLinks'] as $link){
		$getDocID = intval(path_to_id($link));
		if($getDocID){
			$tempDoc = new we_webEditionDocument();
			$tempDoc->initByID($getDocID);
			$lastModified = !empty($tempDoc->ModDate) ? (($tempDoc->ModDate > $lastModified) ? $tempDoc->ModDate : $lastModified) : (($tempDoc->CreationDate > $lastModified) ? $tempDoc->CreationDate : $lastModified);
			unset($tempDoc);
			unset($getDocID);
		}
	}
	return $lastModified;
}


function getMode($docPath){
	global $lv;
	
	$getDocID = path_to_id($docPath);
	we_tag('listview',array('type'=>'document','name' => 'getCategory','cfilter'=>true,'contenttypes'=>'text/webedition','id'=>$getDocID));
	while(we_tag('repeat')){
		$catIDs = implode(',', array_map('intval', explode(',', $GLOBALS['lv']->f('wedoc_Category'))));
		$category = array_filter(we_category::we_getCatsFromIDs($catIDs, $separator, false, $GLOBALS['DB_WE'], "", "Title", "/app/Schulungsinhalte", true));
	}
	we_post_tag_listview();

	return (is_array($category) ? implode(",", $category) : (!empty($category) ? $category : 'treat,learn')); //if no categories given, we set treat,learn as default
}

function processCurlRequest($url, $postdata, $httpAuth){
	$agent = "NEUROLOG API";
	$header[] = "Accept: text/javascript, text/html, application/xml, */*";
	$ch = curl_init($url);
	
	if ($ch){
		curl_setopt($ch,    CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,    CURLOPT_USERAGENT, $agent);
		curl_setopt($ch,    CURLOPT_HTTPHEADER, $header);
		#curl_setopt($ch,    CURLOPT_FOLLOWLOCATION, 1);
		
		# mit den naechsten 2 Zeilen koennte man auch Cookies
		# verwenden und in einem DIR speichern
		#curl_setopt($ch,    CURLOPT_COOKIEJAR, "cookie.txt");
		#curl_setopt($ch,    CURLOPT_COOKIEFILE, "cookie.txt");
		
		if (isset($postdata)){
			curl_setopt($ch,    CURLOPT_POST, 2);
			curl_setopt($ch,    CURLOPT_POSTFIELDS, $postdata);
		}
		
		if(isset($httpAuth)){
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $httpAuth); //Your credentials goes here
		}
		
		$tmp = curl_exec ($ch);
		curl_close ($ch);
	}
	return $tmp;
}

function logApiRequests($errorMessage){

	if(!empty($errorMessage)){
		if(!$handle = fopen($_SERVER['DOCUMENT_ROOT'].'/api/v1/logs/api-logfile-'.date("Ymd").".txt","a")){
			t_e("API Logfile could not be created");
		}else{
			(!fwrite($handle, date("Y-m-d H:i:s")." : ".$errorMessage."\n")) ? t_e('API Logfile is not writable') : '';
		}
		fclose($handle);
	}
}
?>