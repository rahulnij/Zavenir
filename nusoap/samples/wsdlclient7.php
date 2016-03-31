<?php
/*
 *	$Id: wsdlclient7.php,v 1.2 2007/11/06 14:49:10 snichol Exp $
 *
 *	WSDL client sample.
 *
 *	Service: WSDL
 *	Payload: document/literal
 *	Transport: http
 *	Authentication: digest
 */
require_once('../lib/nusoap.php');
$proxyhost = isset($_POST['proxyhost']) ? $_POST['proxyhost'] : '';
$proxyport = isset($_POST['proxyport']) ? $_POST['proxyport'] : '';
$proxyusername = isset($_POST['proxyusername']) ? $_POST['proxyusername'] : '';
$proxypassword = isset($_POST['proxypassword']) ? $_POST['proxypassword'] : '';
$useCURL = isset($_POST['usecurl']) ? $_POST['usecurl'] : '0';
#echo 'You must set your username and password in the source';
#exit();
$client = new nusoap_client("http://180.151.86.86/Zavenir/XRMServices/2011/Organization.svc?singleWsdl", 'wsdl',
						$proxyhost, $proxyport, $proxyusername, $proxypassword);
$client->soap_defencoding = 'UTF-8';
$err = $client->getError();
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}

$client->setUseCurl($useCURL);
$client->loadWSDL();
$client->setCredentials("TRIDENTDELHI\souhardya.chowdhury", "pass@321", 'digest');

$data = array('entity'=>array(
'Attributes' => array(
			'KeyValuePairOfstringanyType' => array('key'=>'new_salutation','value'=>'Mr')
), //tns:AttributeCollection
//'EntityState' => 'Lead', //tns:EntityState
'FormattedValues' => array(),//tns:FormattedValueCollection
'Id' => '',//ser:guid
'LogicalName' => '',//xs:string
'RelatedEntities' => array()//tns:RelatedEntityCollection
)

);

$result = $client->call('Create', $data);
// Check for a fault
if ($client->fault) {
	echo '<h2>Fault</h2><pre>';
	print_r($result);
	echo '</pre>';
} else {
	// Check for errors
	$err = $client->getError();
	if ($err) {
		// Display the error
		echo '<h2>Error</h2><pre>' . $err . '</pre>';
	} else {
		// Display the result
		echo '<h2>Result</h2><pre>';
		print_r($result);
		echo '</pre>';
	}
}
echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';
?>
