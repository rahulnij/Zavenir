<?
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
 
require_once('phpmscrm.inc.php');

if(!IsLogged())
	LocalRedirect('index.php');

$szFormPage = GetHTTPParam('szFormPage', '');
$entityName = GetHTTPParam('entityName', '');
$aParamEntity = GetHTTPParam('aEntity', array());
$aMandatory = GetHTTPParam('aMandatory', array());
$szForwardPage = GetHTTPParam('szForward', 'index.php');
$attributeIdName = GetHTTPParam('attributeIdName', '');

if(($szFormPage == '') || ($entityName == '')) 
	LocalRedirect('index.php');

foreach($aMandatory as $mandatoryField){
	if(empty($aParamEntity[$mandatoryField]) || ($aParamEntity[$mandatoryField] == '') || ($aParamEntity[$mandatoryField] == '0')){
		SetSessionParam('szMessage', '1__You must provide values for all mandatory fields.');
		LocalRedirect($szFormPage.'?'.$attributeIdName.'='.$aParamEntity[$attributeIdName]);
	}
}

$aEntity=array();
$aAttributesParameters = array();
foreach($aParamEntity as $paramEntity => $value){
	//Check if there are any parameters for this attribut
	$aAttrParams = GetHTTPParam($paramEntity, array());
	if(count($aAttrParams)){
		foreach($aAttrParams as $attributeParameterName => $attributeParameterValue)
			$aAttributesParameters[$paramEntity][$attributeParameterName] = $attributeParameterValue;
	}
	$aEntity[$paramEntity] = $value;
}

$crmSoap = new DynamicsCRM();
$result = $crmSoap->updateEntity($entityName, $attributeIdName, $aEntity[$attributeIdName], $aEntity, $aAttributesParameters);

if ($crmSoap->client->fault) { //check for fault
	SetSessionParam('szMessage', '1__ERROR: '.$result['detail']['error']['description']);
	LocalRedirect($szFormPage.'?'.$attributeIdName.'='.$aParamEntity[$attributeIdName]);
}else{ //no fault
	$err = $crmSoap->client->getError();
	if ($err) { // error
		SetSessionParam('szMessage', '1__ERROR: '.var_dump($crmSoap->client->response));
		LocalRedirect($szFormPage.'?'.$attributeIdName.'='.$aParamEntity[$attributeIdName]);
	}else // OK
		LocalRedirect($szFormPage.'?'.$attributeIdName.'='.$aParamEntity[$attributeIdName]);
}
?>