<we:include type="template" id="38" once="true" comment="/app/api/neurolog.class.tmpl"/>
<?php 
// Requests from the same server don't have a HTTP_ORIGIN header
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
	$_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
	$apiCall = new neurolog($_REQUEST['request'], $_SERVER['HTTP_ORIGIN']);
	echo $apiCall->processAPI();
} catch (Exception $e) {
	echo json_encode(Array('error' => $e->getMessage()));
}
?>