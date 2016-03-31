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
$szID = GetHTTPParam('accountid', '');
	
//********Parameters**************************************
//Create New Entity
$szPageTitle = 'New Account';
$formServerPage = 'createEntity.php';
$entityName = 'account';
$attributeIdName = 'accountid';
$aMandatory = array('name');
$result = array();

if($szID != ''){
	//Retreive Entity
	$aEntity = array('name', 'accountnumber', 'parentaccountid', 'customertypecode', 'transactioncurrencyid', 'primarycontactid', 'telephone1', 'telephone2', 'fax', 'websiteurl', 'emailaddress1', 'address1_name', 'address1_line1', 'address1_line2', 'address1_line3', 'address1_postofficebox', 'address1_city', 'address1_stateorprovince', 'address1_postalcode', 'address1_country', 'address1_addresstypecode');

	$crmSoap = new DynamicsCRM();
	$result = $crmSoap->getEntity($entityName, $szID, $aEntity);
	
	if ($crmSoap->client->fault) { //check for fault
		SetSessionParam('szMessage', '1__ERROR: '.$result['detail']['error']['description']);
	}else{ //no fault
		$err = $crmSoap->client->getError();
		if ($err) { // error
			SetSessionParam('szMessage', '1__ERROR: '.var_dump($crmSoap->client->response));
		}else{ // OK
			$szPageTitle = 'Account: '.$result['RetrieveResult']['name'];
			$formServerPage = 'updateEntity.php';
		}
	}
}
//********END Parameters**********************************

require_once('header.inc.php');

if(GetHTTPParam('closewindow', '') == 'yes')
	echo '<script type="text/javascript">window.close();</script>';
?>
<h1><?=$szPageTitle?></h1>
<form action="<?=$formServerPage?>" method="post" onsubmit="return checkMandatoryFileds()">
	<table class="toolbarTable clsNoPrint">
		<tr>
			<td>
				<input type="submit" value="Save" />&nbsp;
				<?
				if($szID != ''){
				?>
				<button type="button" onclick="deleteEntry('<?=$szID?>', '<?=$entityName?>', '<?=$_SERVER['PHP_SELF']?>')">Delete</button>
				<?
				}
				?>
			</td>
		</tr>
	</table>
	<br />
	<fieldset>
	<legend>General</legend>
	<table class="formTable">
		<col style="width:10%;" />
		<col style="width:40%;background:#fff;" />
		<col style="width:10%;" />
		<col style="width:40%;background:#fff;" />
		<tr>
			<?=displayTextInput('name', 'Account Name', $result, true)?>
			<?=displayTextInput('telephone1', 'Main Phone', $result)?>
		</tr>
		<tr>
			<?=displayTextInput('accountnumber', 'Account Number', $result)?>
			<?=displayTextInput('telephone2', 'Other Phone', $result)?>
		</tr>
		<tr>
			<td><label>Parent Account:</label></td>
			<td onclick="lookup('account', 'parentaccountid', 'parentaccountidLabel')" class="lookupField">
				<span id="parentaccountidLabel" style="float:left;"><?=(!empty($result['RetrieveResult']['parentaccountid']['!']))? $result['RetrieveResult']['parentaccountid']['!name'] : ''?></span>
				<input type="hidden" id="parentaccountid" name="aEntity[parentaccountid]" value="<?=(!empty($result['RetrieveResult']['parentaccountid']['!']))? $result['RetrieveResult']['parentaccountid']['!'] : ''?>" />
			</td>
			<?=displayTextInput('fax', 'Fax', $result)?>
		</tr>
		<tr>
			<td><label>Primary Contact:</label></td>
			<td onclick="lookup('contact', 'primarycontactid', 'primarycontactidLabel')" class="lookupField">
				<span id="primarycontactidLabel" style="background:#fff;float:left;"><?=(!empty($result['RetrieveResult']['primarycontactid']['!name']))? $result['RetrieveResult']['primarycontactid']['!name'] : ''?></span>
				<input type="hidden" id="primarycontactid" name="aEntity[primarycontactid]" value="<?=(!empty($result['RetrieveResult']['primarycontactid']['!name']))? $result['RetrieveResult']['primarycontactid']['!'] : ''?>" />
			</td>
			<?=displayTextInput('websiteurl', 'Web Site', $result)?>
		</tr>
		<tr>
			<td><label>Relationship Type:</label></td>
			<td>
				<?
				$customertypecode = (!empty($result['RetrieveResult']['customertypecode']['!']))? $result['RetrieveResult']['customertypecode']['!'] : 0;
				?>
				<select id="customertypecode" name="aEntity[customertypecode]">
					<option value=""></option>
					<option value="1" <?=($customertypecode == 1)? 'selected="selected"' : ''; ?>>Competitor</option>
					<option value="2" <?=($customertypecode == 2)? 'selected="selected"' : ''; ?>>Consultant</option>
					<option value="3" <?=($customertypecode == 3)? 'selected="selected"' : ''; ?>>Customer</option>
					<option value="4" <?=($customertypecode == 4)? 'selected="selected"' : ''; ?>>Investor</option>
					<option value="5" <?=($customertypecode == 5)? 'selected="selected"' : ''; ?>>Partner</option>
					<option value="6" <?=($customertypecode == 6)? 'selected="selected"' : ''; ?>>Influencer</option>
					<option value="7" <?=($customertypecode == 7)? 'selected="selected"' : ''; ?>>Press</option>
					<option value="8" <?=($customertypecode == 8)? 'selected="selected"' : ''; ?>>Prospect</option>
					<option value="9" <?=($customertypecode == 9)? 'selected="selected"' : ''; ?>>Reseller</option>
					<option value="10" <?=($customertypecode == 10)? 'selected="selected"' : ''; ?>>Supplier</option>
					<option value="11" <?=($customertypecode == 11)? 'selected="selected"' : ''; ?>>Vendor</option>
					<option value="12" <?=($customertypecode == 12)? 'selected="selected"' : ''; ?>>Other</option>
				</select>
			</td>
			<?=displayTextInput('emailaddress1', 'E-mail', $result)?>
		</tr>
		<tr>
			<td><label>Currency:</label></td>
			<td onclick="lookup('transactioncurrency', 'transactioncurrencyid', 'transactioncurrencyidLabel')" class="lookupField">
				<span id="transactioncurrencyidLabel" style="float:left;"><?=(!empty($result['RetrieveResult']['transactioncurrencyid']['!name']))? $result['RetrieveResult']['transactioncurrencyid']['!name'] : ''?></span>
				<input type="hidden" id="transactioncurrencyid" name="aEntity[transactioncurrencyid]" value="<?=(!empty($result['RetrieveResult']['transactioncurrencyid']['!']))? $result['RetrieveResult']['transactioncurrencyid']['!'] : ''?>" />
			</td>
			<td colspan="2"></td>
		</tr>
	</table>
</fieldset>	
<br />
<fieldset>
	<legend>Address</legend>
	<table class="formTable">
		<col style="width:10%;" />
		<col style="width:40%;background:#fff;" />
		<col style="width:10%;" />
		<col style="width:40%;background:#fff;" />
		<tr>
			<?=displayTextInput('address1_name', 'Address Name', $result)?>
			<?=displayTextInput('address1_stateorprovince', 'State/Province', $result)?>
		</tr>
		<tr>
			<?=displayTextInput('address1_line1', 'Street 1', $result)?>
			<?=displayTextInput('address1_postalcode', 'ZIP/Postal Code', $result)?>
		</tr>
		<tr>
			<?=displayTextInput('address1_line2', 'Street 2', $result)?>
			<?=displayTextInput('address1_country', 'Country/Region', $result)?>
		</tr>
		<tr>
			<?=displayTextInput('address1_line3', 'Street 3', $result)?>
			<td><label>Address Type:</label></td>
			<td>
				<?
				$address1_addresstypecode = (!empty($result['RetrieveResult']['address1_addresstypecode']['!']))? $result['RetrieveResult']['address1_addresstypecode']['!'] : 0;
				?>
				<select id="address1_addresstypecode" name="aEntity[address1_addresstypecode]">
					<option value=""></option>
					<option value="1" <?=($address1_addresstypecode == 1)? 'selected="selected"' : ''; ?>>Bill To</option>
					<option value="2" <?=($address1_addresstypecode == 2)? 'selected="selected"' : ''; ?>>Ship To</option>
					<option value="3" <?=($address1_addresstypecode == 3)? 'selected="selected"' : ''; ?>>Primary</option>
					<option value="4" <?=($address1_addresstypecode == 4)? 'selected="selected"' : ''; ?>>Other</option>
				</select>				
			</td>
		</tr>
		<tr>
			<?=displayTextInput('address1_postofficebox', 'Post Office Box', $result)?>
			<td colspan="2"></td>
		</tr>
		<tr>
			<?=displayTextInput('address1_city', 'City', $result)?>
			<td colspan="2"></td>
		</tr>
	</table>
</fieldset>
<?php
foreach($aMandatory as $mandatoryField)
	echo '<input type="hidden" name="aMandatory[]" value="'.$mandatoryField.'" />';
?>
<input type="hidden" name="entityName" value="<?=$entityName?>" />
<input type="hidden" name="attributeIdName" value="<?=$attributeIdName?>" />
<input type="hidden" name="szForward" value="<?=$_SERVER['PHP_SELF']?>" />
<input type="hidden" name="szFormPage" value="<?=$_SERVER['PHP_SELF']?>" />
<input type="hidden" name="aEntity[<?=$attributeIdName?>]" value="<?=$szID?>" />
</form>
</body>
</html>