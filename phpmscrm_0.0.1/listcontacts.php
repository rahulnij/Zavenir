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
	
//********Parameters**************************************
$szSearchString = GetHTTPParam('search', '');
$nPageNumber = GetHTTPParam('page', 1);
$szLetterFilter = GetHTTPParam('l', '');
$nViewFilter = GetHTTPParam('view', 0);
$sortAttributeName = GetHTTPParam('sort', '');
$sortDirection = GetHTTPParam('sortDirection', 'Ascending');
$itemsPerPage = GetHTTPParam('items', 20);
$nPreviousViewFilter = GetHTTPParam('previousView', 'False');

if($szSearchString != '')
	$nViewFilter = 3;

$szPageTitle = 'Contacts';
$szEditEntityScript = 'contact.php';
$entityName = 'contact';
$attributeIdName = 'contactid';
//Attribute on which search will be performed
$searchAttributeName = 'fullname';
//Attribute on which sorting will be performed
if($sortAttributeName == '')
	$sortAttributeName = 'fullname';
//Status to use for Filtered View: Active, Open ...
$statusFilterCondition = 'Active';
//Columns to retreive
$aEntity = array('fullname', 'parentcustomerid', 'jobtitle', 'telephone1', 'emailaddress1');
$aEntityLabels = array('fullname' => 'Full Name', 'parentcustomerid' => 'Parent Customer', 'jobtitle' => 'Job Title', 'telephone1' => 'Business Phone', 'emailaddress1' => 'E-mail');
//View Filters
$viewFilters = array(0 => 'My Active Contacts', 1 => 'Active Contacts');
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

//Starting Letter Filter
if($szLetterFilter != ''){
	$aLetterCondition['attributeName'] = $searchAttributeName;
	$aLetterCondition['operator'] = 'Like';
	$aLetterCondition['values'] = array($szLetterFilter.'%');
	$aCriteria[] = $aLetterCondition;
}

//View filter
if(($nViewFilter == 0) && ($szSearchString == '')){
	$aViewCondition['attributeName'] = 'ownerid';
	$aViewCondition['operator'] = 'Equal';
	$aViewCondition['values'] = array(GetSessionParam('crmUserId'));
	$aCriteria[] = $aViewCondition;
}

//Status Condition
$aStatusCondition['attributeName'] = 'statecode';
$aStatusCondition['operator'] = 'Equal';
$aStatusCondition['values'] = array($statusFilterCondition);
$aCriteria[] = $aStatusCondition;

$aEntityOrders=array();
$aEntityOrders[$sortAttributeName] = $sortDirection;

require_once('header.inc.php');
?>
<h1><?=$szPageTitle?></h1>
<table class="toolbarTable">
	<tr>
		<td>
			<form action="<?=$_SERVER['PHP_SELF']?>" method="get">
				Search:
				<input type="text" id="search" name="search" style="width:200px;" value="<?=($szSearchString != '')? $szSearchString : ''?>" />
				<input type="submit" value="Search" />
				<?
				if($szSearchString != '')
					echo '<a href="'.$_SERVER['PHP_SELF'].'?view='.$nPreviousViewFilter.'&amp;items='.$itemsPerPage.'">Clear search</a>';
				?>
				<input type="hidden" id="previousView" name="previousView" value="<?=$nViewFilter?>" />
				<input type="hidden" id="items" name="items" value="<?=$itemsPerPage?>" />
			</form>
		</td>
		<td style="text-align:right;"><?=displayViewFilter($viewFilters, $nViewFilter, $szSearchString, $itemsPerPage);?></td>
	</tr>
</table>
<form action="deleteEntities.php" method="post" onsubmit="return deleteEntries()">
<table class="toolbarTable">
	<tr>
		<td>
			<button type="button" onclick="window.location.href='<?=$szEditEntityScript?>'">New</button>
			<input type="submit" value="Delete" />
		</td>
		<td style="text-align:right;"><?=itemsPerPageSelector($nViewFilter, $sortAttributeName, $sortDirection, $szSearchString, $itemsPerPage);?></td>
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
		displayViewHeader($aEntityLabels, $sortAttributeName, $sortDirection, $nViewFilter, $itemsPerPage, $szSearchString);
		if($result['RetrieveMultipleResult']['BusinessEntities'] != ''){
			if(!empty($result['RetrieveMultipleResult']['BusinessEntities']['BusinessEntity'][$attributeIdName]))
				$businessEntitiesList = $result['RetrieveMultipleResult']['BusinessEntities'];
			else
				$businessEntitiesList = $result['RetrieveMultipleResult']['BusinessEntities']['BusinessEntity'];
			
			foreach ($businessEntitiesList AS $entity){
				?>
				<tr>
					<td><input type="checkbox" name="entityId[]" value="<?=$entity[$attributeIdName]?>" /></td>
					<td><a href="<?=$szEditEntityScript?>?<?=$attributeIdName?>=<?=$entity[$attributeIdName]?>" target="_blank"><?=(!empty($entity[$searchAttributeName]))? $entity[$searchAttributeName] : ''?></a></td>
					<td><?=(!empty($entity['parentcustomerid']['!name']))? $entity['parentcustomerid']['!name']: ''?></td>
					<td><?=(!empty($entity['jobtitle']))? $entity['jobtitle'] : ''?></td>
					<td><?=(!empty($entity['telephone1']))? $entity['telephone1'] : ''?></td>
					<td><?=(!empty($entity['emailaddress1']))? $entity['emailaddress1'] : ''?></td>
				</tr>		
				<?
			}
		}else{
			echo '<tr><td colspan="'.(count($aEntity)+1).'" style="text-align:center;"><br /><br />No records are available in this view<br /><br /><br /></td></tr>';
		}
		echo '</table>';
		displayNavigationTable($nViewFilter, $sortAttributeName, $sortDirection, $szSearchString, $szLetterFilter, $nPageNumber, $result['RetrieveMultipleResult']['!MoreRecords'], $itemsPerPage);
	}
}
?>
<input type="hidden" name="entityName" value="<?=$entityName?>" />
<input type="hidden" name="szForward" value="<?=$_SERVER['PHP_SELF']?>" />
</form>
</body>
</html>