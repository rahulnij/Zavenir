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
$szID = GetHTTPParam('activityid', '');

//********Parameters**************************************
$szPageTitle = 'New Activity';
$formServerPage = 'createEntity.php';
$entityName = 'activitypointer';
$attributeIdName = 'activityid';
$aMandatory = array('subject', 'activitytypecode');
$result = array();

if($szID != ''){
	//Retreive Entity
	$crmSoap = new DynamicsCRM();
	$aEntity=array('subject', 'activitytypecode');
	$result = $crmSoap->getEntity($entityName, $szID, $aEntity);	
	
	if ($crmSoap->client->fault) { //check for fault
		SetSessionParam('szMessage', '1__ERROR: '.$result['detail']['error']['description']);
	}else{ //no fault
		$err = $crmSoap->client->getError();
		if ($err) { // error
			SetSessionParam('szMessage', '1__ERROR: '.var_dump($crmSoap->client->response));
		}else{ // OK
			$szPageTitle = 'Activity: '.$result['RetrieveResult']['subject'];
			$formServerPage = 'updateEntity.php';
		}
	}	
}
//********END Parameters**********************************

require_once('header.inc.php');
?>
<h1><?=$szPageTitle?></h1>
<form action="<?=$formServerPage?>" method="post" onsubmit="return checkMandatoryFileds()">
<table class="toolbarTable clsNoPrint"><tr><td><input type="submit" value="Save" /></td></tr></table>
<br />
<fieldset>
	<legend>General</legend>
	<table class="formTable">
		<col style="width:10%;" />
		<col style="width:40%;background:#fff;" />
		<col style="width:10%;" />
		<col style="width:40%;background:#fff;" />
		<tr>
			<?=displayTextInput('subject', 'Subject', $result, true, true)?>
			<?=displayTextInput('activitytypecode', 'Activity Type', $result, false, true)?>
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