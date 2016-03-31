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
$szID = GetHTTPParam('opportunityid', '');
	
//********Parameters**************************************
$szPageTitle = 'New Opportunity';
$formServerPage = 'createEntity.php';
$entityName = 'opportunity';
$attributeIdName = 'opportunityid';
$aMandatory = array('name', 'customerid','transactioncurrencyid');
$result = array();

if($szID != ''){
	//Retreive Entity
	$aEntity = array('name', 'customerid', 'estimatedvalue', 'closeprobability', 'actualvalue', 'transactioncurrencyid', 'stepname', 'prioritycode');

	$crmSoap = new DynamicsCRM();
	$result = $crmSoap->getEntity($entityName, $szID, $aEntity);
	
	if ($crmSoap->client->fault) { //check for fault
		SetSessionParam('szMessage', '1__ERROR: '.$result['detail']['error']['description']);
	}else{ //no fault
		$err = $crmSoap->client->getError();
		if ($err) { // error
			SetSessionParam('szMessage', '1__ERROR: '.var_dump($crmSoap->client->response));
		}else{ // OK
			$szPageTitle = 'Opportunity: '.$result['RetrieveResult']['name'];
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
			<td colspan="3"><input type="text" id="name" name="aEntity[name]" value="<?=(!empty($result['RetrieveResult']['name']))? $result['RetrieveResult']['name'] : ''?>" /></td>
		</tr>
		<tr>
			<td><label>Potential Customer <span style="color:red;">*</span>:</label></td>
			<td onclick="lookup('account', 'customerid', 'customeridLabel')" class="lookupField">
				<span id="customeridLabel" style="float:left;"><?=(!empty($result['RetrieveResult']['customerid']['!']))? $result['RetrieveResult']['customerid']['!name'] : ''?></span>
				<input type="hidden" id="customerid" name="aEntity[customerid]" value="<?=(!empty($result['RetrieveResult']['customerid']['!']))? $result['RetrieveResult']['customerid']['!'] : ''?>" />			 	 
				<input type="hidden" id="customeridType" name="customerid[type]" value="account" />
			</td>
			<td><label>Priority:</label></td>
			<td>
				<?
				$prioritycode = (!empty($result['RetrieveResult']['prioritycode']['!']))? $result['RetrieveResult']['prioritycode']['!'] : 0;
				?>
				<select id="prioritycode" name="aEntity[prioritycode]">
					<option value="2" <?=($prioritycode == 2)? 'selected="selected"' : ''; ?>>Yes</option>
					<option value="3" <?=($prioritycode == 3)? 'selected="selected"' : ''; ?>>No</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><label>Currency <span style="color:red;">*</span>:</label></td>
			<td onclick="lookup('transactioncurrency', 'transactioncurrencyid', 'transactioncurrencyidLabel')" class="lookupField">
				<span id="transactioncurrencyidLabel" style="float:left;"><?=(!empty($result['RetrieveResult']['transactioncurrencyid']['!name']))? $result['RetrieveResult']['transactioncurrencyid']['!name'] : ''?></span>
				<input type="hidden" id="transactioncurrencyid" name="aEntity[transactioncurrencyid]" value="<?=(!empty($result['RetrieveResult']['transactioncurrencyid']['!']))? $result['RetrieveResult']['transactioncurrencyid']['!'] : ''?>" />
			</td>
			<td colspan="2"></td>
		</tr>
		<tr>
			<?=displayTextInput('stepname', 'Step Name', $result)?>
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