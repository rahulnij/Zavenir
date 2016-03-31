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
$szID = GetHTTPParam('leadid', '');

//********Parameters**************************************
$szPageTitle = 'New Lead';
$formServerPage = 'createEntity.php';
$entityName = 'lead';
$attributeIdName = 'leadid';
$aMandatory = array('subject', 'lastname', 'companyname');
$result = array();

if($szID != ''){
	//Retreive Entity
	$aEntity = array('subject', 'firstname', 'lastname', 'companyname', 'companyname', 'salutation', 'jobtitle', 'leadqualitycode', 'transactioncurrencyid', 'telephone1', 'telephone2', 'telephone3', 'fax', 'pager', 'emailaddress1', 'mobilephone', 'websiteurl', 'description');

	$crmSoap = new DynamicsCRM();
	$result = $crmSoap->getEntity($entityName, $szID, $aEntity);
	
	if ($crmSoap->client->fault) { //check for fault
		SetSessionParam('szMessage', '1__ERROR: '.$result['detail']['error']['description']);
	}else{ //no fault
		$err = $crmSoap->client->getError();
		if ($err) { // error
			SetSessionParam('szMessage', '1__ERROR: '.var_dump($crmSoap->client->response));
		}else{ // OK
			$szPageTitle = 'Lead: '.$result['RetrieveResult']['subject'];
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
			<td><label>Topic <span style="color:red;">*</span>:</label></td>
			<td colspan="3"><input type="text" id="subject" name="aEntity[subject]" value="<?=(!empty($result['RetrieveResult']['subject']))? $result['RetrieveResult']['subject'] : ''?>" /></td>
		</tr>
		<tr>
			<?=displayTextInput('domainname', 'First Name', $result)?>
			<?=displayTextInput('title', 'Title', $result)?>
		</tr>
		<tr>
			<?=displayTextInput('lastname', 'Last Name', $result, true)?>
			<?=displayTextInput('jobtitle', 'Job Title', $result)?>
		</tr>
		<tr>
			<?=displayTextInput('companyname', 'Company Name', $result, true)?>
			<td><label>Rating:</label></td>
			<td>
				<?
				$leadqualitycode = (!empty($result['RetrieveResult']['leadqualitycode']['!']))? $result['RetrieveResult']['leadqualitycode']['!'] : 0;
				?>
				<select id="leadqualitycode" name="aEntity[leadqualitycode]">
					<option value=""></option>
					<option value="1" <?=($leadqualitycode == 1)? 'selected="selected"' : ''; ?>>Hot</option>
					<option value="2" <?=($leadqualitycode == 2)? 'selected="selected"' : ''; ?>>Warm</option>
					<option value="3" <?=($leadqualitycode == 3)? 'selected="selected"' : ''; ?>>Cold</option>
				</select>
			</td>
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
	<legend>Contact Information</legend>
	<table class="formTable">
		<col style="width:10%;" />
		<col style="width:40%;background:#fff;" />
		<col style="width:10%;" />
		<col style="width:40%;background:#fff;" />
		<tr>
			<?=displayTextInput('telephone1', 'Business Phone', $result)?>
			<?=displayTextInput('fax', 'Fax', $result)?>
		</tr>
		<tr>
			<?=displayTextInput('telephone2', 'Home Phone', $result)?>
			<?=displayTextInput('pager', 'Pager', $result)?>
		</tr>
		<tr>
			<?=displayTextInput('telephone3', 'Other Phone', $result)?>
			<?=displayTextInput('emailaddress1', 'E-mail', $result)?>
		</tr>
		<tr>
			<?=displayTextInput('mobilephone', 'Mobile Phone', $result)?>
			<?=displayTextInput('websiteurl', 'Web Site', $result)?>
		</tr>
	</table>
</fieldset>
<br />
<fieldset>
	<legend>Description</legend>
	<table class="formTable">
		<tr>
			<td>
				<textarea id="description" name="aEntity[description]" rows="5" cols="20"><?=(!empty($result['RetrieveResult']['description']))? $result['RetrieveResult']['description'] : ''?></textarea>
			</td>
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