<?php
/**
 * PHPMSCRM
 *
 * Copyright (c) 2010 PHPMSCRM
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPMSCRM
 * @package    PHPMSCRM
 * @copyright  Copyright (c) 2010 PHPMSCRM (http://phpmscrm.codeplex.com/)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    0.0.1, 2010-08-20
 */
 
require_once('libraries/nusoap/lib/nusoap.php');
require_once('libraries/nusoap/lib/class.wsdlcache.php');

class DynamicsCRM extends nusoap_client {
	var $organisationName = CRM_ORG_NAME;
	var $wsdl = CRM_WSDL;
	
	var $namespace = 'http://schemas.microsoft.com/crm/2007/WebServices';
	
	function __construct() {
		$szUserName = GetSessionParam('szUserName');
		$szPassword = GetSessionParam('szPassword');

		//Cache wsdl to improve XML parsing performance
		$cache = new wsdlcache('.', 60);
		$wsdl = $cache->get($this->wsdl);
		if (is_null($wsdl)) {
			//Use this portion if you don't have a local copy of the wsdl file and you need to authentify on the distant server
			$wsdl = new wsdl('', '', '', '', '', 5);
			// set up HTTP auth login/pwd
			$wsdl->setCredentials($szUserName, $szPassword, 'ntlm');
			// Now we can fetch the wsdl data.
			$wsdl->fetchWSDL($this->wsdl);
			$cache->put($wsdl);
		} else {
			$wsdl->clearDebug();
			$wsdl->debug('Retrieved from cache');
		}
		
		$this->client = new nusoap_client($wsdl, 'wsdl');
		//$this->client = new nusoap_client($this->wsdl, 'wsdl');	

		$this->client->setCredentials($szUserName, $szPassword, 'ntlm');
		$this->client->setUseCURL(true);
		$this->client->useHTTPPersistentConnection();
		$this->client->soap_defencoding = 'UTF-8';
	}

	function call($operation, $soapBody) {
		$soapBody = '<' . $operation . ' xmlns="' . $this->namespace . '">' .
			$soapBody .
		'</' . $operation . '>';

		return $this->client->call(
			$operation,
			$soapBody,
			$this->namespace,
			null,
			$this->generateSoapHeader()
		);		
	}

	function generateSoapHeader() {
		return '<CrmAuthenticationToken xmlns="' . $this->namespace . '">' .
			'<AuthenticationType xmlns="http://schemas.microsoft.com/crm/2007/CoreTypes">0</AuthenticationType>' .
			'<OrganizationName xmlns="http://schemas.microsoft.com/crm/2007/CoreTypes">'.$this->organisationName.'</OrganizationName>' .
			'<CallerId xmlns="http://schemas.microsoft.com/crm/2007/CoreTypes">00000000-0000-0000-0000-000000000000</CallerId>' .
		'</CrmAuthenticationToken>';
	}
	
	function getCurrentUserInfo(){
		return $this->call('Execute', '<Request xsi:type="WhoAmIRequest" />'); 
	}

	function getListOfEntites($entityName, $attributesList=array(), $aAccountOrders=array(), $filterOperator='', $searchCriteria=array(), $pageNumber=0, $itemsPerPage=0){
		if(!count($attributesList))
			return false;
			
		//prepare attributs
		$attributesListString = '';
		foreach($attributesList as $attribute){
			if(!empty($attribute) && ($attribute != '')){
				$attributesListString .= '<q1:Attribute>';
				$attributesListString .= $attribute;
				$attributesListString .= '</q1:Attribute>';
			}
		}
		
		if($attributesListString == '')
			return false;
		
		//Set Search criteria
		$searchCriteriaString = '';
		if(($filterOperator != '') && count($searchCriteria)){
			if(count($searchCriteria)){
				$searchCriteriaString .= '<q1:Criteria>';
				$searchCriteriaString .= '<q1:FilterOperator>'.$filterOperator.'</q1:FilterOperator>';
				$searchCriteriaString .= '<q1:Conditions>';
				foreach($searchCriteria as $condition){
					$searchCriteriaString .= '<q1:Condition>';
					$searchCriteriaString .= '<q1:AttributeName>'.$condition['attributeName'].'</q1:AttributeName>';
					$searchCriteriaString .= '<q1:Operator>'.$condition['operator'].'</q1:Operator>';
					$searchCriteriaString .= '<q1:Values>';
					foreach($condition['values'] as $conditionValue){
						$searchCriteriaString .= '<q1:Value xsi:type="xsd:string">'.$conditionValue.'</q1:Value>';
					}
					$searchCriteriaString .= '</q1:Values>';
					$searchCriteriaString .= '</q1:Condition>';
				}
				$searchCriteriaString .= '</q1:Conditions>';
				$searchCriteriaString .= '</q1:Criteria>';
			}
		}
		
		//Set results order
		$orderString = '';
		if(count($aAccountOrders)){
			$orderString = '<q1:Orders>';
			foreach($aAccountOrders as $orderAttributeName => $orderType){
				$orderString .= '<q1:Order>';
				$orderString .= 	'<q1:AttributeName>'.$orderAttributeName.'</q1:AttributeName>';
				$orderString .= 	'<q1:OrderType>'.$orderType.'</q1:OrderType>';
				$orderString .= '</q1:Order>';
			}
			$orderString .= '</q1:Orders>';
		}
			
		return $this->call(
			'RetrieveMultiple',
				'<query xmlns:q1="http://schemas.microsoft.com/crm/2006/Query" xsi:type="q1:QueryExpression">' .
				'<q1:EntityName>'.$entityName.'</q1:EntityName>' .
				'<q1:ColumnSet xsi:type="q1:ColumnSet">' .
					'<q1:Attributes>' .
						$attributesListString .
					'</q1:Attributes>' .
				'</q1:ColumnSet>' .
				$searchCriteriaString .
				$orderString .				
				'<q1:Distinct>false</q1:Distinct>' .
				'<q1:PageInfo>' .
					 '<q1:PageNumber>'.$pageNumber.'</q1:PageNumber>' .
					 '<q1:Count>'.$itemsPerPage.'</q1:Count>' .

				'</q1:PageInfo>' .
				'</query>'
		); 
	}
	
	function createEntity($entityName, $valuesList=array(), $aAttributesParameters=array()){
		if(!count($valuesList))
			return false;
			
		//prepare attributs and values
		$valuesListString = '';
		foreach($valuesList as $key=>$value){
			if(!empty($key) && !empty($value) && ($key != '') && ($value != '')){
				$valuesListString .= '<'.$key;
				if(!empty($aAttributesParameters[$key])){
					foreach($aAttributesParameters[$key] as $attributeParameterName => $attributeParameterValue){
						$valuesListString .= ' '.$attributeParameterName.'="'.$attributeParameterValue.'"';
					}
				}
				$valuesListString .= '>';
				$valuesListString .= $value;
				$valuesListString .= '</'.$key.'>';
			}
		}
		
		if($valuesListString == '')
			return false;
			
		//Create Object
		return $this->call(
			'Create',
				'<entity xsi:type="'.$entityName.'">' .
				$valuesListString .
				'</entity>'
		);
	}
	
	function getEntity($entityName, $GUID, $attributesList=array()){
		if(!count($attributesList))
			return false;
			
		//prepare attributs
		$attributesListString = '';
		foreach($attributesList as $attribute){
			if(!empty($attribute) && ($attribute != '')){
				$attributesListString .= '<q1:Attribute>';
				$attributesListString .= $attribute;
				$attributesListString .= '</q1:Attribute>';
			}
		}
		
		if($attributesListString == '')
			return false;
		
		return $this->call(
			'Retrieve',
				'<entityName>'.$entityName.'</entityName>'.
				'<id>'.$GUID.'</id>'.
				'<columnSet xmlns:q1="http://schemas.microsoft.com/crm/2006/Query" xsi:type="q1:ColumnSet">'. 
					'<q1:Attributes> '.
						$attributesListString .
					'</q1:Attributes>'.
				'</columnSet>'
		);
	}
	
	function deleteEntity($entityName, $GUID){
		if(empty($entityName) || ($entityName == '')) return false;
		if(empty($GUID) || ($GUID == '')) return false;
			
		return $this->call(
			'Delete',
				'<entityName>'.$entityName.'</entityName>'.
				'<id>'.$GUID.'</id>'
		);	
	}
	
	function updateEntity($entityName, $idFieldName, $GUID, $valuesList=array(), $aAttributesParameters=array()){
		if(empty($entityName) || ($entityName == '')) return false;
		if(empty($idFieldName) || ($idFieldName == '')) return false;
		if(empty($GUID) || ($GUID == '')) return false;
		if(!count($valuesList)) return false;
			
		//prepare attributs and values
		$valuesListString = '';
		foreach($valuesList as $key=>$value){
			if(!empty($key) && !empty($value) && ($key != '') && ($value != '')){
				$valuesListString .= '<'.$key;
				if(!empty($aAttributesParameters[$key])){
					foreach($aAttributesParameters[$key] as $attributeParameterName => $attributeParameterValue){
						$valuesListString .= ' '.$attributeParameterName.'="'.$attributeParameterValue.'"';
					}
				}
				$valuesListString .= '>';
				$valuesListString .= $value;
				$valuesListString .= '</'.$key.'>';
			}
		}
		
		if($valuesListString == '')
			return false;
			
		//Create Object
		return $this->call(
			'Update',
				'<entity xsi:type="'.$entityName.'">' .
					'<'.$idFieldName.'>'.$GUID.'</'.$idFieldName.'>'.
					$valuesListString .
				'</entity>'
		);
	}
}

/*
$crmSoap = new DynamicsCRM();

//Account Creation Example
$aAccount=array();
$aAccount['name'] = 'la companie de zoran';
$aAccount['new_assimaregion'] = '8';
$aAccount['accountclassificationcode'] = '3';
$result = $crmSoap->createEntity('account', $aAccount);

//Lead Creation Example
$aLead=array();
$aLead['subject'] = 'toto topic';
$aLead['lastname'] = 'ivanov';
$aLead['companyname'] = 'assima';
$result = $crmSoap->createEntity('lead', $aLead);

//Retreive Multiple Leads Example
$aLead=array();
$aLead[] = 'subject';
$aLead[] = 'companyname';
$result = $crmSoap->getListOfEntites('lead', $aLead);

//Search Accounts Example
$aAccount=array();
$aAccount[] = 'name';

$aAccountFilterOperator = 'And';
$aAccountSearchCriteria=array();

$aAccountSearchCondition1 = array();
$aAccountSearchCondition1['attributeName'] = 'new_assimaregion';
$aAccountSearchCondition1['operator'] = 'Like';
$aAccountSearchCondition1['values'] = array('1');
$aAccountSearchCriteria[] = $aAccountSearchCondition1;

$aAccountSearchCondition2 = array();
$aAccountSearchCondition2['attributeName'] = 'createdby';
$aAccountSearchCondition2['operator'] = 'Like';
$aAccountSearchCondition2['values'] = array('CA48BCB3-AC2A-DF11-A865-0003FF1136C8');
$aAccountSearchCriteria[] = $aAccountSearchCondition2;

$aAccountOrders=array();
$aAccountOrders['name'] = 'Ascending';
$result = $crmSoap->getListOfEntites('account', $aAccount, $aAccountOrders, $aAccountFilterOperator, $aAccountSearchCriteria);
/*
//Retreive 1 Account Example
$aAccount=array();
$aAccount[] = 'name';
$aAccount[] = 'address1_line1';
$aAccount[] = 'address1_line2';
$aAccount[] = 'address1_stateorprovince';
$aAccount[] = 'address1_city';
$aAccount[] = 'address1_postalcode';
$aAccount[] = 'telephone1';
$result = $crmSoap->getEntity('account', 'A5BBF8F2-F9B1-DB11-9E18-001372A314AA', $aAccount);

//Delete 1 Lead Example
$result = $crmSoap->deleteEntity('lead', 'B8B993AE-8078-DF11-AD14-0003FF1136C8');

//Account Update Example
$aAccount=array();
$aAccount['name'] = 'companie de zoran';
$aAccount['emailaddress1'] = 'zivanov@assima.net';
$result = $crmSoap->updateEntity('account', 'accountid', 'B6B993AE-8078-DF11-AD14-0003FF1136C8', $aAccount);


//result
if ($crmSoap->client->fault) { //check for fault
	echo '<p><b>Fault: ';
	print_r($result);
	echo '</b></p>';
}else{ //no fault
	$err = $crmSoap->client->getError();
	if ($err) { // error
		echo 'Error: ' . $err . '';
		echo "<br /><br /># # # # # # # Request # # # # # # #<br />";
		var_dump($crmSoap->client->request);
		echo "<br /><br /># # # # # # Response # # # # # # #<br />";
		var_dump($crmSoap->client->response);
	}
	else { // display the result
	print_r($result);
	}
}
*/
