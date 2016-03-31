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
$szID = GetHTTPParam('contactid', '');
	
//********Parameters**************************************
$entityName = 'contact';
$attributeIdName = 'contactid';
$aMandatory = array('lastname');
$result = array();

if($szID != ''){
	//Retreive Entity
	$aEntity = array('lastname', 'firstname', 'middlename', 'fullname', 'salutation', 'parentcustomerid', 'jobtitle', 'telephone1', 'telephone2', 'telephone3', 'mobilephone', 'emailaddress1', 'transactioncurrencyid', 'fax', 'address1_name', 'address1_line1', 'address1_line2', 'address1_line3', 'address1_postofficebox', 'address1_city', 'address1_stateorprovince', 'address1_postalcode', 'address1_country', 'address1_addresstypecode', 'address1_telephone2', 'pager', 'address1_shippingmethodcode');

	$crmSoap = new DynamicsCRM();
	$result = $crmSoap->getEntity($entityName, $szID, $aEntity);

	$szPageTitle = 'Contact: '.$result['RetrieveResult']['fullname'];
	$formServerPage = 'updateEntity.php';
}else{
	//Create New Entity
	$szPageTitle = 'New Contact';
	$formServerPage = 'createEntity.php';
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
			<?=displayTextInput('salutation', 'Salutation', $result)?>
			<?=displayTextInput('telephone1', 'Business Phone', $result)?>
		</tr>
		<tr>
			<?=displayTextInput('firstname', 'First Name', $result)?>
			<?=displayTextInput('telephone2', 'Business Phone 2', $result)?>
		</tr>
		<tr>
			<?=displayTextInput('middlename', 'Middle Name', $result)?>
			<?=displayTextInput('mobilephone', 'Mobile Phone', $result)?>
		</tr>
		<tr>
			<?=displayTextInput('lastname', 'Last Name', $result, true)?>
			<?=displayTextInput('telephone3', 'Company Main Phone', $result)?>
		</tr>
		<tr>
			<?=displayTextInput('jobtitle', 'Job Titlee', $result)?>
			<?=displayTextInput('fax', 'Fax', $result)?>
		</tr>
		<tr>
			<td><label>Parent Customer:</label></td>
			<td onclick="lookup('account', 'parentcustomerid', 'parentcustomeridLabel')" class="lookupField">
				<span id="parentcustomeridLabel" style="float:left;"><?=(!empty($result['RetrieveResult']['parentcustomerid']['!']))? $result['RetrieveResult']['parentcustomerid']['!name'] : ''?></span>
				<input type="hidden" id="parentcustomerid" name="aEntity[parentcustomerid]" value="<?=(!empty($result['RetrieveResult']['parentcustomerid']['!']))? $result['RetrieveResult']['parentcustomerid']['!'] : ''?>" />
			</td>
			<?=displayTextInput('pager', 'Pager', $result)?>
		</tr>
		<tr>
			<td><label>Currency:</label></td>
			<td onclick="lookup('transactioncurrency', 'transactioncurrencyid', 'transactioncurrencyidLabel')" class="lookupField">
				<span id="transactioncurrencyidLabel" style="float:left;"><?=(!empty($result['RetrieveResult']['transactioncurrencyid']['!name']))? $result['RetrieveResult']['transactioncurrencyid']['!name'] : ''?></span>
				<input type="hidden" id="transactioncurrencyid" name="aEntity[transactioncurrencyid]" value="<?=(!empty($result['RetrieveResult']['transactioncurrencyid']['!']))? $result['RetrieveResult']['transactioncurrencyid']['!'] : ''?>" />
			</td>
			<?=displayTextInput('emailaddress1', 'E-mail', $result)?>
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
			<td><label for="address1_addresstypecode">Address Type:</label></td>
			<td>
				<?
				$address1_addresstypecode = (!empty($result['RetrieveResult']['address1_addresstypecode']['!']))? $result['RetrieveResult']['address1_addresstypecode']['!'] : 0;
				?>
				<select id="address1_addresstypecode" name="aEntity[address1_addresstypecode]">
					<option value=""></option>
					<option value="1" <?=($address1_addresstypecode  == 1)? 'selected="selected"' : ''; ?>>Bill To</option>
					<option value="2" <?=($address1_addresstypecode  == 2)? 'selected="selected"' : ''; ?>>Ship To</option>
					<option value="3" <?=($address1_addresstypecode  == 3)? 'selected="selected"' : ''; ?>>Primary</option>
					<option value="4" <?=($address1_addresstypecode  == 4)? 'selected="selected"' : ''; ?>>Other</option>
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