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
	
//********Parameters**************************************
$szSearchString = GetHTTPParam('search', '');
$nPageNumber = GetHTTPParam('page', 1);
$nViewFilter = GetHTTPParam('view', 0);
$sortAttributeName = GetHTTPParam('sort', '');
$sortDirection = GetHTTPParam('sortDirection', 'Ascending');
$entityName = GetHTTPParam('entity', '');
$sourceInputId = GetHTTPParam('inputid', '');
$sourceLabelId = GetHTTPParam('labelid', '');

if($entityName == '')
	LocalRedirect('index.php');
	
if($szSearchString != '')
	$nViewFilter = 3;

$szPageTitle = 'Look Up Records';
if($entityName == 'account'){
	$szEditEntityScript = 'account.php';
	$attributeIdName = 'accountid';
	//Attribute on which search will be performed
	$searchAttributeName = 'name';
	//Attribute on which sorting will be performed
	if($sortAttributeName == '')
		$sortAttributeName = 'name';
	//Status to use for Filtered View: Active, Open ...
	$statusFilterAttribute = 'statecodename';
	$statusFilterCondition = 'Active';
	//Columns to retreive
	$aEntity = array('name', 'telephone1', 'emailaddress1');
	$aEntityLabels = array('name' => 'Account Name', 'telephone1' => 'Main Phone', 'emailaddress1' => 'E-mail');
}elseif($entityName == 'contact'){
	$szEditEntityScript = 'contact.php';
	$attributeIdName = 'contactid';
	//Attribute on which search will be performed
	$searchAttributeName = 'fullname';
	//Attribute on which sorting will be performed
	if($sortAttributeName == '')
		$sortAttributeName = 'fullname';
	//Status to use for Filtered View: Active, Open ...
	$statusFilterAttribute = 'statecodename';
	$statusFilterCondition = 'Active';
	//Columns to retreive
	$aEntity = array('fullname', 'parentcustomerid', 'jobtitle', 'telephone1', 'emailaddress1');
	$aEntityLabels = array('fullname' => 'Full Name', 'parentcustomerid' => 'Parent Customer', 'jobtitle' => 'Job Title', 'telephone1' => 'Business Phone', 'emailaddress1' => 'E-mail');
}elseif($entityName == 'systemuser'){
	$szEditEntityScript = 'user.php';
	$attributeIdName = 'systemuserid';
	//Attribute on which search will be performed
	$searchAttributeName = 'fullname';
	//Attribute on which sorting will be performed
	if($sortAttributeName == '')
		$sortAttributeName = 'fullname';
	//Status to use for Filtered View: Active, Open ...
	$statusFilterAttribute = 'isdisabled';
	$statusFilterCondition = 'False';
	//Columns to retreive
	$aEntity = array('fullname', 'siteid', 'businessunitid', 'title', 'isdisabled');
	$aEntityLabels = array('fullname' => 'Full Name', 'siteid' => 'Site', 'businessunitid' => 'Business Unit', 'title' => 'Title');
}elseif($entityName == 'transactioncurrency'){
	$szEditEntityScript = '#';
	$attributeIdName = 'transactioncurrencyid';
	//Attribute on which search will be performed
	$searchAttributeName = 'currencyname';
	//Attribute on which sorting will be performed
	if($sortAttributeName == '')
		$sortAttributeName = 'currencyname';
	//Status to use for Filtered View: Active, Open ...
	$statusFilterAttribute = 'statuscode';
	$statusFilterCondition = 'Active';
	//Columns to retreive
	$aEntity = array('currencyname', 'isocurrencycode', 'currencysymbol', 'exchangerate');
	$aEntityLabels = array('currencyname' => 'Currency Name', 'isocurrencycode' => 'ISO Currency Code', 'currencysymbol' => 'Currency Symbol', 'exchangerate' => 'Exchange Rate');
}else{
	LocalRedirect('index.php');
}

//Set number of Items/page to display
$itemsPerPage = 20;
//********End Parameters***********************************

$aFilterOperator = 'And';
$aCriteria=array();

$aSearchCondition = array();
if($szSearchString != ''){
	$aSearchCondition['attributeName'] = $searchAttributeName;
	$aSearchCondition['operator'] = 'Like';
	$aSearchCondition['values'] = array('%'.$szSearchString.'%');
	$aCriteria[] = $aSearchCondition;
}

$aEntityOrders=array();
$aEntityOrders[$sortAttributeName] = $sortDirection;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head> 
<meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" /> 
<title><?=$szPageTitle?></title> 
<link rel="stylesheet" type="text/css" href="style.css" /> 
<script type="text/javascript" src="script.js"></script> 
</head> 
<body>  
<h1><?=$szPageTitle?></h1>
<table class="toolbarTable">
	<tr>
		<td>
			Look for:
			<select disabled="disabled">
				<option value='contact' <?=($entityName == 'contact')? 'selected="selected"' : ''?>>Contacts</option>
				<option value='account' <?=($entityName == 'account')? 'selected="selected"' : ''?>>Accounts</option>
				<option value='systemuser' <?=($entityName == 'systemuser')? 'selected="selected"' : ''?>>Users</option>
				<option value='transactioncurrency' <?=($entityName == 'transactioncurrency')? 'selected="selected"' : ''?>>Currency</option>
			</select>
		</td>
		<td style="text-align:right;">
			<form action="<?=$_SERVER['PHP_SELF']?>" method="get">
				Search:
				<input type="text" id="search" name="search" style="width:200px;" value="<?=($szSearchString != '')? $szSearchString : ''?>" />
				<input type="submit" value="Search" />
				<input type="hidden" name="entity" value="<?=$entityName?>" />
				<input type="hidden" name="inputid" value="<?=$sourceInputId?>" />
				<input type="hidden" name="labelid" value="<?=$sourceLabelId?>" />
			</form>
		</td>
	</tr>
</table>
<?			
//Retreive Results
$crmSoap = new DynamicsCRM();
$result = $crmSoap->getListOfEntites($entityName, $aEntity, $aEntityOrders, $aFilterOperator, $aCriteria, $nPageNumber, $itemsPerPage);		 
   
//result
if ($crmSoap->client->fault) { //check for fault
	displaySoapFault($result);
}else{ //no fault
	$err = $crmSoap->client->getError();
	if ($err) { // error
		displayCRMError($err, $crmSoap->client->request, $crmSoap->client->response);
	}else{ // display the result
		echo '<table class="sortable">';		
		echo '<tr><th style="width:10px;"></th>';
			
	foreach($aEntityLabels as $columnAttributeName => $columnDisplayName){
			echo '<th>';   
			if(($sortAttributeName == $columnAttributeName) && ($sortDirection == 'Ascending'))
				echo '<a href="'.$_SERVER['PHP_SELF'].'?items='.$itemsPerPage.'&amp;inputid='.$sourceInputId.'&amp;labelid='.$sourceLabelId.'&amp;entity='.$entityName.'&amp;sort='.$columnAttributeName.'&amp;sortDirection=Descending&amp;search='.$szSearchString.'" title="Sort by '.$columnDisplayName.'">'.$columnDisplayName.'&nbsp;&#9650;</a>';
			elseif($sortAttributeName == $columnAttributeName)
				echo '<a href="'.$_SERVER['PHP_SELF'].'?items='.$itemsPerPage.'&amp;inputid='.$sourceInputId.'&amp;labelid='.$sourceLabelId.'&amp;entity='.$entityName.'&amp;sort='.$columnAttributeName.'&amp;sortDirection=Ascending&amp;search='.$szSearchString.'" title="Sort by '.$columnDisplayName.'">'.$columnDisplayName.'&nbsp;&#9660;</a>';
			else
				echo '<a href="'.$_SERVER['PHP_SELF'].'?items='.$itemsPerPage.'&amp;inputid='.$sourceInputId.'&amp;labelid='.$sourceLabelId.'&amp;entity='.$entityName.'&amp;sort='.$columnAttributeName.'&amp;sortDirection=Ascending&amp;search='.$szSearchString.'" title="Sort by '.$columnDisplayName.'">'.$columnDisplayName.'&nbsp;</a>';
			echo '</th>';
		}
		echo '</tr>';
		
		if($result['RetrieveMultipleResult']['BusinessEntities'] != ''){
			if(!empty($result['RetrieveMultipleResult']['BusinessEntities']['BusinessEntity'][$attributeIdName]))
				$businessEntitiesList = $result['RetrieveMultipleResult']['BusinessEntities'];
			else
				$businessEntitiesList = $result['RetrieveMultipleResult']['BusinessEntities']['BusinessEntity'];
			
			$cpt = 1;
			foreach ($businessEntitiesList AS $entity){
				?>
				<tr>
					<td><input type="radio" id="record_<?=$cpt?>" name="entityId[]" value="<?=$entity[$attributeIdName]?>" /></td>
					<td><a  id="record_<?=$cpt?>_label" href="<?=$szEditEntityScript?>?<?=$attributeIdName?>=<?=$entity[$attributeIdName]?>" target="_blank"><?=(!empty($entity[$searchAttributeName]))? $entity[$searchAttributeName] : ''?></a></td>
					<?
					if($entityName == 'account'){
					?>
					<td><?=(!empty($entity['telephone1']))? $entity['telephone1']: ''?></td>
					<td><?=(!empty($entity['emailaddress1']))? $entity['emailaddress1'] : ''?></td>
					<?
					}elseif($entityName == 'contact'){
					?>
					<td><?=(!empty($entity['parentcustomerid']['!name']))? $entity['parentcustomerid']['!name']: ''?></td>
					<td><?=(!empty($entity['jobtitle']))? $entity['jobtitle'] : ''?></td>
					<td><?=(!empty($entity['telephone1']))? $entity['telephone1'] : ''?></td>
					<td><?=(!empty($entity['emailaddress1']))? $entity['emailaddress1'] : ''?></td>
					<?
					}elseif($entityName == 'systemuser'){
					?>
					<td><?=(!empty($entity['siteid']['!name']))? $entity['siteid']['!name'] : ''?></td>
					<td><?=(!empty($entity['businessunitid']['!name']))? $entity['businessunitid']['!name'] : ''?></td>
					<td><?=(!empty($entity['title']))? $entity['title'] : ''?></td>
					<?
					}elseif($entityName == 'transactioncurrency'){
						?>
						<td><?=(!empty($entity['isocurrencycode']))? $entity['isocurrencycode'] : ''?></td>
						<td><?=(!empty($entity['currencysymbol']))? $entity['currencysymbol'] : ''?></td>
						<td><?=(!empty($entity['exchangerate']['!']))? $entity['exchangerate']['!'] : ''?></td>
						<?
					}
					?>
				</tr>		
				<?
				$cpt++;
			}
		}else{
			echo '<tr><td colspan="'.(count($aEntity)+1).'" style="text-align:center;"><br /><br />No records are available in this view<br /><br /><br /></td></tr>';
		}
		echo '</table><br /><br /><br />';
	}
}
?>
<table style="border-top:1px solid #C4CAD1;padding:5px;width:100%;position:fixed;bottom:0px;left:0px;height:25px;overflow:hidden;background: #F0F0F0;">
	<tr>
		<td style="white-space:nowrap;">
			<?
			//Pages navigation	
			if($nPageNumber > 1)
				echo '<th style="width:20px;"><a href="'.$_SERVER['PHP_SELF'].'?page='.($nPageNumber-1).'&amp;entity='.$entityName.'&amp;inputid='.$sourceInputId.'&amp;labelid='.$sourceLabelId.'&amp;view='.$nViewFilter.'&amp;items='.$itemsPerPage.'&amp;sort='.$sortAttributeName.'&amp;sortDirection='.$sortDirection.'&amp;search='.$szSearchString.'" style="font-family: Arial;" title="Previous Page">&#9668;</a></th>';
			echo '<th style="white-space:nowrap;width:30px;">Page&nbsp;'.$nPageNumber.'</th>';
			if($result['RetrieveMultipleResult']['!MoreRecords'] == '1')
				echo '<th style="width:20px;"><a href="'.$_SERVER['PHP_SELF'].'?page='.($nPageNumber+1).'&amp;entity='.$entityName.'&amp;inputid='.$sourceInputId.'&amp;labelid='.$sourceLabelId.'&amp;view='.$nViewFilter.'&amp;items='.$itemsPerPage.'&amp;sort='.$sortAttributeName.'&amp;sortDirection='.$sortDirection.'&amp;search='.$szSearchString.'" style="font-family: Arial;" title="Next Page">&#9658;</a></th>';
			?>
		<td>
		<td style="text-align:right;white-space:nowrap;">
			<button onclick="selectLookupValue('<?=$sourceInputId?>', '<?=$sourceLabelId?>', false)">Ok</button>&nbsp;
			<button  onclick="window.close()">Cancel</button>&nbsp;
			<button onclick="selectLookupValue('<?=$sourceInputId?>', '<?=$sourceLabelId?>', true)">Remove Value</button>
			&nbsp;&nbsp;&nbsp;
		</td>
	</tr>
</table>
</body>
</html>