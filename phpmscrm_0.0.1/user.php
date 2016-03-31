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

if(!IsLogged()){
	SetSessionParam('szMessage', '1__You must login before you can access this page.');
	SetSessionParam('szRedirect', $_SERVER['PHP_SELF']. RebuildQuery());
	LocalRedirect('index.php');
}

//Retreive parameters
$szID = GetHTTPParam('systemuserid', '');
	
//********Parameters**************************************
$szPageTitle = 'New User';
$formServerPage = 'createEntity.php';
$entityName = 'systemuser';
$attributeIdName = 'systemuserid';
$aMandatory = array('');
$result = array();

if($szID != ''){
	//Retreive Entity
	$aEntity = array('fullname', 'title', 'domainname', 'firstname', 'lastname', 'internalemailaddress');

	$crmSoap = new DynamicsCRM();
	$result = $crmSoap->getEntity($entityName, $szID, $aEntity);
	
	if ($crmSoap->client->fault) { //check for fault
		SetSessionParam('szMessage', '1__ERROR: '.$result['detail']['error']['description']);
	}else{ //no fault
		$err = $crmSoap->client->getError();
		if ($err) { // error
			SetSessionParam('szMessage', '1__ERROR: '.var_dump($crmSoap->client->response));
		}else{ // OK
			$szPageTitle = 'User: '.$result['RetrieveResult']['fullname'];
			$formServerPage = 'updateEntity.php';
		}
	}
}
//********END Parameters**********************************

require_once('header.inc.php');
?>
<h1><?=$szPageTitle?></h1>
<br />
<fieldset>
	<legend>General</legend>
	<table class="formTable">
		<col style="width:10%;" />
		<col style="width:40%;background:#fff;" />
		<col style="width:10%;" />
		<col style="width:40%;background:#fff;" />
		<tr>
			<?=displayTextInput('domainname', 'Domain Logon Name', $result, true, true)?>
			<?=displayTextInput('title', 'Title', $result, false, true)?>
		</tr>
		<tr>
			<?=displayTextInput('firstname', 'First Name', $result, true, true)?>
			<?=displayTextInput('lastname', 'Last Name', $result, true, true)?>
		</tr>
		<tr>
			<?=displayTextInput('internalemailaddress', 'Primary E-mail', $result, false, true)?>
			<td colspan="2"></td>
		</tr>
	</table>
</fieldset>
</body>
</html>