<?php
require_once('../lib/nusoap.php');
//configuration
$your_organization='CRM2';
$login ='EUROPE\zivanov';
$pass ='izoranco';
$useCURL = true;

$client = new nusoap_client('http://newserver2003/MSCrmServices/2007/CrmServiceWsdl.aspx?uniquename=CRM2', true);
$client->setCredentials($login, $pass, 'ntlm');
$client->setUseCurl(true);
$client->useHTTPPersistentConnection();
$client->soap_defencoding = 'UTF-8';	
/*	
$err = $client->getError();
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
	exit();
}

//create contact
$soapHeader='<soap:Header>' .
	'<CrmAuthenticationToken xmlns="http://schemas.microsoft.com/crm/2007/WebServices">' .
		'<AuthenticationType xmlns="http://schemas.microsoft.com/crm/2007/CoreTypes">0</AuthenticationType>' .
		'<OrganizationName xmlns="http://schemas.microsoft.com/crm/2007/CoreTypes">'.$your_organization.'</OrganizationName>' .
		'<CallerId xmlns="http://schemas.microsoft.com/crm/2007/CoreTypes">00000000-0000-0000-0000-000000000000</CallerId>' .
	'</CrmAuthenticationToken>' .
	'</soap:Header>';

$soapBody='<soap:Body>' .
	'<Create xmlns="http://schemas.microsoft.com/crm/2007/WebServices">' .
	'<entity xsi:type="contact">' .
		'<firstname>firstName</firstname>' .
		'<lastname>test555LastName</lastname>' .
	'</entity>' .
	'</Create>' .
	'</soap:Body>';

//prepare the SOAP message.
$xml = '<?xml version="1.0" encoding="utf-8"?>' .
	'<soap:Envelope' .
		' xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"' .
		' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' .
		' xmlns:xsd="http://www.w3.org/2001/XMLSchema">' .
	$soapHeader .
	$soapBody .
	'</soap:Envelope>';

//prepare header
$headers = array(
	'SOAPAction' => 'http://schemas.microsoft.com/crm/2007/WebServices/Create',
	'Content-Type' => 'text/xml; charset="utf-8""',
	'Content-Length' => strlen($xml)
	);

//SOAP call
$result = $client->call('Create',
	$xml,
	'http://schemas.microsoft.com/crm/2007/WebServices',
	'http://schemas.microsoft.com/crm/2007/WebServices/Create',
	$headers);
	

//result
if ($client->fault) { //check for fault
	echo '<p><b>Fault: ';
	print_r($result);
	echo '</b></p>';
}

else { //no fault
	$err = $client->getError();
	if ($err) { // error
		echo 'Error: ' . $err . '';
		echo "<br /><br /># # # # # # # Request # # # # # # #<br />";
		var_dump($client->request);
		echo "<br /><br /># # # # # # Response # # # # # # #<br />";
		var_dump($client->response);
	}
	else { // display the result
	print_r($result);
	}
}
*/

$err = $client->getError();
echo 'test';
if ($err) {
	echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
	echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
	exit();
}
echo 'test';

//create phonecall

//prepare the SOAP message.
$xml = '<?xml version="1.0" encoding="utf-8"?>' .
	'<soap:Envelope' .
		' xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"' .
		' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' .
		' xmlns:xsd="http://www.w3.org/2001/XMLSchema">' .
	'<soap:Header>' .
		'<CrmAuthenticationToken xmlns="http://schemas.microsoft.com/crm/2007/WebServices">' .
		'<AuthenticationType xmlns="http://schemas.microsoft.com/crm/2007/CoreTypes">0</AuthenticationType>' .
		'<OrganizationName xmlns="http://schemas.microsoft.com/crm/2007/CoreTypes">'.$your_organization.'</OrganizationName>' .
		'<CallerId xmlns="http://schemas.microsoft.com/crm/2007/CoreTypes">00000000-0000-0000-0000-000000000000</CallerId>' .
		'</CrmAuthenticationToken>' .
	'</soap:Header>'.
	'<soap:Body>' .
		'<Create xmlns="http://schemas.microsoft.com/crm/2007/WebServices">' .
		'<entity xsi:type="phonecall">' .
			'<subject>test subject</subject>' .
			'<description>some description</description>' .
			'<ownerid>583B9B5C-FD1C-DE11-BE83-005056A9676C</ownerid>' .
		'</entity>' .
		'</Create>' .
	'</soap:Body>' .
	'</soap:Envelope>';

//prepare header
$headers = array(
	'SOAPAction' => 'http://schemas.microsoft.com/crm/2007/WebServices/Create',
	'Content-Type' => 'text/xml; charset="utf-8""',
	'Content-Length' => strlen($xml)
	);

//SOAP call
$result = $client->call('Create',
	$xml,
	'http://schemas.microsoft.com/crm/2007/WebServices',
	'http://schemas.microsoft.com/crm/2007/WebServices/Create',
	$headers);

//result
if ($client->fault) { echo 'Fault'; }
else { //no fault
	$err = $client->getError();
	if ($err) { echo 'Error: ' . $err . ''; }
	else { print_r($result); }
}
?>