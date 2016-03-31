<?php
ini_set('default_socket_timeout', 5);
$client = new SoapClient('http://180.151.86.86/Zavenir/XRMServices/2011/Organization.svc?singleWsdl', array('login'          => "TRIDENTDELHI\souhardya.chowdhury",
                                            'password'       => "pass@321",'soap_version'   => SOAP_1_2,
                                            'trace' => true,
											
                                            ));

$data = array('entity'=>array(
'Attributes' => array(
			'KeyValuePairOfstringanyType' => array('key'=>'new_salutation','value'=>'Mr')
), //tns:AttributeCollection
'EntityState' => 'Lead', //tns:EntityState
'FormattedValues' => array(),//tns:FormattedValueCollection
'Id' => '',//ser:guid
'LogicalName' => '',//xs:string
'RelatedEntities' => array()//tns:RelatedEntityCollection
)

);
try {
	echo $client->create($data);
	echo '<pre>';
	print_r($client->__getFunctions());

	
} catch(Exception $e) {
 var_dump($client->__getLastRequestHeaders(),$client->__getLastResponse(),$client->__getLastResponseHeaders());
 var_dump($e->getMessage());
 echo '</pre>';
 
 echo $client->__getLastRequest();
 
}

