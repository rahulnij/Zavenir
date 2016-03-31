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
 
require('crmsoap.inc.php');

// start/resume session
session_start();

require_once('config.inc.php');

$db = mysql_connect(MYSQL_DB_SERVER, MYSQL_DB_USER, MYSQL_DB_PASSWORD);
if ($db === FALSE){
	echo("Could not connect to the database.<br />");
}

if (!mysql_select_db(MYSQL_DB_NAME, $db)){
	echo 'Could not connect to the database. Please try again.<br />';
}

// return vIf if condition is true, vElse otherwise
function _if($bCondition, $vIf, $vElse = ''){
	return ($bCondition ? $vIf : $vElse);
}

//run query
function QueryDatabase($szQuery){
	return mysql_query($szQuery);
}

// retrieve array with POST/GET variables
function GetHTTPParamArray()
{
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
		return $_POST;
	elseif ($_SERVER['REQUEST_METHOD'] == 'GET')
		return $_GET;
	else
		return array();
}

// retrieve parameters passed through the GET/POST method
// this is for security reasons
// (register_globals is off to prevent malicious users from setting variables through the HTTP request)
function GetHTTPParam($szParam, $vDefault = '', $bTrim = TRUE)
{
	$aParams = GetHTTPParamArray();
	if (!isset($aParams[$szParam]))
	{
		//DbgLog('Value for ' . $szParam . ': default value "' . $vDefault . '"', __FILE__, __LINE__);
		if (is_string($vDefault))
			return ($bTrim)? trim($vDefault) : $vDefault;
		return $vDefault;
	}
	//DbgLog('Value for ' . $szParam . ': value "' . $aParams[$szParam] . '"', __FILE__, __LINE__);
	if (is_string($aParams[$szParam]))
		return ($bTrim)? trim($aParams[$szParam]) : $aParams[$szParam];
	return $aParams[$szParam];
}

// rebuild query part
function RebuildQuery($aNewVars = array())
{
	$szQuery = '';
	$aVars = GetHTTPParamArray();
	foreach ($aVars as $szVar => $szVal)
	{
		if ($szVar != ''){
			if (is_array($szVal)){
				foreach ($szVal as $szArrayVal)
					$szQuery .= _if($szArrayVal, _if($szQuery, '&a', '?') . $szVar . '[]=' . urlencode($szArrayVal));
			}
			else
				$szQuery .= _if($szVar, _if($szQuery, '&', '?') . $szVar . '=' . urlencode($szVal));
		}
	}
	foreach ($aNewVars as $szNewVar)
		$szQuery .= _if($szNewVar, _if($szQuery, '&', '?') . $szNewVar);
		
	return $szQuery;
}

//set session paramter
function SetSessionParam($szParam, $szValue){
	$_SESSION[$szParam] = $szValue;
}

//get session parameter
function GetSessionParam($szParam, $bArray = FALSE){
	if (isset($_SESSION[$szParam])){
		return $_SESSION[$szParam];
	}
	else{
		return ($bArray)? array(): '';
	}
}

//unset session parameter
function UnsetSessionParam($szParam){
	$_SESSION[$szParam] = '';
	unset($_SESSION[$szParam]);
}

// log user out
function UnlogUser(){
	if (isset($_COOKIE['DYNAMICSCRMAUTH'])){
		// invalidate permanent cookie
		$szWizAuth = 'DYNAMICSCRMAUTH=0; expires=' . gmdate("D, d-M-Y H:i:s \G\M\T", time() - (60*60*24));	// 60*60*24 = one day
		header('Set-Cookie: ' . $szWizAuth);
		UpdateUser(GetSessionParam('idUser'), array('szPermHash' => ''));
	}
	//DbgLog('logging out', __FILE__, __LINE__);
	session_unset();
}

function CreateUser($aUser)
{
	$idUser = FALSE;
	$aInsert = array('dCreated');
	$aValues = array('"'.date('Y-m-j H:i:s').'"');
	foreach ($aUser as $szField => $szValue){
		$aInsert[] = $szField;
		$aValues[] = QuoteEscape($szValue);
	}
	if (QueryDatabase('INSERT INTO tusers (' . join(',', $aInsert) . ') VALUES (' . join(',', $aValues) . ')'))
		$idUser = mysql_insert_id();
	return $idUser;
}

function UpdateUser($idUser, $aUser)
{
	$aSet = array();
	foreach ($aUser as $szField => $szValue)
		$aSet[] = $szField . '=' . QuoteEscape($szValue);
	if (count($aSet) > 0){
		return QueryDatabase('UPDATE tusers SET ' . join(',', $aSet) . ' WHERE idUser=' . QuoteEscape($idUser));
	}
	return TRUE;
}

// log user in
function LogUser($aUser){
	session_unset();
	if (isset($_SERVER['HTTP_X_CLIENTIP']))
		$szIP = $_SERVER['HTTP_X_CLIENTIP'];
	else if (isset($_SERVER['REMOTE_ADDR']))
		$szIP = $_SERVER['REMOTE_ADDR'];
	else
		$szIP = '';

	$szHost = $szIP;
		
	SetSessionParam('idUser', $aUser['idUser']);
	SetSessionParam('szUser', $aUser['szUser']);
	SetSessionParam('szUserName', $aUser['szUserName']);
	SetSessionParam('szPassword', $aUser['szPassword']);

	QueryDatabase('UPDATE tusers SET dLastLogin=NOW() WHERE idUser=' . QuoteEscape($aUser['idUser']));

	$szPermanent = strtolower(md5($aUser['szUserName'] . '|' . $aUser['szPassword']));
	$szWizAuth = 'DYNAMICSCRMAUTH=' . $aUser['idUser'] . 'a' . $szPermanent . '; expires=' . gmdate("D, d-M-Y H:i:s \G\M\T", time() + (60*60*24*31)) . '; DYNAMICSCRM';	// 60*60*24*31 = one month
	header('Set-Cookie: ' . $szWizAuth);
	UpdateUser($aUser['idUser'], array('szPermHash' => $szPermanent));
}

// check if user is properly logged on
function IsLogged($bAutoLogon = TRUE){
	$idUser = GetSessionParam('idUser');
	if (!$idUser)
		return FALSE;
	return true;
}

function GetListOfUsers($szWhere, $szOrderBy)
{
	if ($szWhere != '')		$szWhere = ' WHERE ' . $szWhere;
	if ($szOrderBy != '')	$szOrderBy = ' ORDER BY ' . $szOrderBy;
	return QueryDatabase(
		'SELECT * FROM tusers ' .
		$szWhere .
		$szOrderBy
		);
}

//redirect page
function LocalRedirect($szPage){
	if (isset($_SERVER['HTTP_HOST']) && ($_SERVER['HTTP_HOST'] != ''))
		$szHost = $_SERVER['HTTP_HOST'];
	else
		$szHost = 'localhost';
	session_write_close();
	//header('Location: ' . 'http://' . $_SERVER['HTTP_HOST'] . '/' . trim($szPage, '/'));
	$list = explode('/', $szPage);
	header('Location: ' . $list[count($list)-1]);
	die();
}

function format_float($float){
	$result = $float;
	if(preg_match("*[0-9],[0-9]*", $float))
		$result = str_replace(',', '.', $float);
	return $result;
}

function QuoteEscape($szValue){
	if (!get_magic_quotes_gpc())
	return '"' . addslashes($szValue) . '"';
	else
		return '"' . $szValue . '"';
	
}

//Display View Header with Columns titles
function displayViewHeader($aEntityLabels, $sortAttributeName, $sortDirection, $nViewFilter, $itemsPerPage, $szSearchString){
	echo '<tr><th style="width:10px;"><input type="checkbox" value="" onclick="toggleSelectAllItems(this)" /></th>';
	foreach($aEntityLabels as $columnAttributeName => $columnDisplayName){
		echo '<th>';
		if(($sortAttributeName == $columnAttributeName) && ($sortDirection == 'Ascending'))
			echo '<a href="'.$_SERVER['PHP_SELF'].'?view='.$nViewFilter.'&amp;items='.$itemsPerPage.'&amp;sort='.$columnAttributeName.'&amp;sortDirection=Descending&amp;search='.$szSearchString.'" title="Sort by '.$columnDisplayName.'">'.$columnDisplayName.'&nbsp;&#9650;</a>';
		elseif($sortAttributeName == $columnAttributeName)
			echo '<a href="'.$_SERVER['PHP_SELF'].'?view='.$nViewFilter.'&amp;items='.$itemsPerPage.'&amp;sort='.$columnAttributeName.'&amp;sortDirection=Ascending&amp;search='.$szSearchString.'" title="Sort by '.$columnDisplayName.'">'.$columnDisplayName.'&nbsp;&#9660;</a>';
		else
			echo '<a href="'.$_SERVER['PHP_SELF'].'?view='.$nViewFilter.'&amp;items='.$itemsPerPage.'&amp;sort='.$columnAttributeName.'&amp;sortDirection=Ascending&amp;search='.$szSearchString.'" title="Sort by '.$columnDisplayName.'">'.$columnDisplayName.'&nbsp;</a>';
		echo '</th>';
	}
	echo '</tr>';
}

//Display View Filter
function displayViewFilter($viewFilters, $nViewFilter, $szSearchString, $itemsPerPage){
	?>
	View:
	<select id="viewFilter" name="viewFilter" onchange="switchView('<?='http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']?>', this.value, <?=$itemsPerPage?>)">
		<?
		foreach($viewFilters as $filterValue => $filterLabel){
			?>
			<option value="<?=$filterValue?>" <?=($nViewFilter == $filterValue)? 'selected="selected"' : ''?>><?=$filterLabel?></option>
			<?
		}
		if($szSearchString != ''){
		?>
		<option value="3" selected="selected">Search results</option>
		<?
		}
		?>
	</select>
	<?
}

//Display Navigation table for Entites View
function displayNavigationTable($nViewFilter, $sortAttributeName, $sortDirection, $szSearchString, $szLetterFilter, $nPageNumber, $moreRecords, $itemsPerPage){
	?>
	<table class="footerTable">
		<tr>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">All</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=A&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">A</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=B&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">B</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=C&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">C</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=D&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">D</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=E&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">E</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=F&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">F</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=G&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">G</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=H&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">H</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=I&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">I</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=J&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">J</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=K&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">K</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=L&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">L</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=M&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">M</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=N&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">N</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=O&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">O</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=P&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">P</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=Q&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">Q</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=R&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">R</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=S&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">S</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=T&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">T</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=U&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">U</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=V&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">V</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=W&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">W</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=X&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">X</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=Y&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">Y</a></th>
			<th><a href="<?=$_SERVER['PHP_SELF']?>?l=Z&amp;view=<?=$nViewFilter?>&amp;items=<?=$itemsPerPage?>&amp;sort=<?=$sortAttributeName?>&amp;sortDirection=<?=$sortDirection?>&amp;search=<?=$szSearchString?>">Z</a></th>
	<?
	//Pages navigation	
	if($nPageNumber > 1)
		echo '<th style="width:20px;"><a href="'.$_SERVER['PHP_SELF'].'?l='.$szLetterFilter.'&amp;page='.($nPageNumber-1).'&amp;view='.$nViewFilter.'&amp;items='.$itemsPerPage.'&amp;sort='.$sortAttributeName.'&amp;sortDirection='.$sortDirection.'&amp;search='.$szSearchString.'" style="font-family: Arial;" title="Previous Page">&#9668;</a></th>';
	echo '<th style="white-space:nowrap;width:30px;">Page&nbsp;'.$nPageNumber.'</th>';
	if($moreRecords == '1')
		echo '<th style="width:20px;"><a href="'.$_SERVER['PHP_SELF'].'?l='.$szLetterFilter.'&amp;page='.($nPageNumber+1).'&amp;view='.$nViewFilter.'&amp;items='.$itemsPerPage.'&amp;sort='.$sortAttributeName.'&amp;sortDirection='.$sortDirection.'&amp;search='.$szSearchString.'" style="font-family: Arial;" title="Next Page">&#9658;</a></th>';
	?>
		</tr>		
	</table>
	<?
}

//Display Items per page selector
function itemsPerPageSelector($nViewFilter, $sortAttributeName, $sortDirection, $szSearchString, $itemsPerPage){
	?>
	Show:
	<select onchange="switchItemsNumber('<?=$_SERVER['PHP_SELF']?>', '<?=$nViewFilter?>', this.value, '<?=$sortAttributeName?>', '<?=$sortDirection?>', '<?=$szSearchString?>')">
		<option value="20" <?=($itemsPerPage ==20)? 'selected="selected"' : ''?>>20</option>
		<option value="100" <?=($itemsPerPage ==100)? 'selected="selected"' : ''?>>100</option>
		<option value="500" <?=($itemsPerPage ==500)? 'selected="selected"' : ''?>>500</option>
		<option value="1000" <?=($itemsPerPage ==1000)? 'selected="selected"' : ''?>>1000</option>
	</select>
	records
	<?
}

//Display Currency symbol
function currencySymbol($currencyName){
	if($currencyName == "euro") 
		return "€";
	elseif($currencyName == "US Dollar") 
		return "$";
	elseif($currencyName == "Pound Sterling") 
		return "£";
	elseif($currencyName == "Canadian Dollar") 
		return "$";
	elseif($currencyName == "Dansk krone") 
		return "kr";
	elseif($currencyName == "Franc suisse") 
		return "SFr.";
	else 
		return "";
}

//Display Soap Fault
function displaySoapFault($result){
	echo '<b style="color:red;">ERROR: '.$result['faultstring'].'</b><br />';
	echo 'Detail: '.$result['detail']['error']['description'].'<br />';
}

//Display CRM Error
function displayCRMError($err, $request, $response){
	echo '<b style="color:red;">ERROR: ' . $err . '</b><br />';
	echo "____________Request____________<br />";
	var_dump($request);
	echo "<br /><br />____________Response____________<br />";
	var_dump($response);
}

function displayTextInput($id='', $label='', $result=array(), $isMandatory=false, $isDisabled=false){
	echo '<td><label for="'.$id.'">'.$label;
	echo ($isMandatory)? ' <span style="color:red;">*</span>' : '';
	echo ':</label></td>';
	echo '<td><input type="text" id="'.$id.'" name="aEntity['.$id.']"'; 
	echo ($isDisabled)? ' disabled="disabled" ' : '';
	echo' value="';
	echo (!empty($result['RetrieveResult'][$id]))? $result['RetrieveResult'][$id] : '';
	echo'" /></td>';
}

//displayLookupInput('customerid', 'account', 'Potential Customer', $result, true, false);
function displayLookupInput($id='', $objectType, $label='', $result=array(), $isMandatory=false, $isDisabled=false){
	echo '<td><label>'.$label;
	echo ($isMandatory)? ' <span style="color:red;">*</span>' : '';
	echo ':</label></td>';
	echo ($isDisabled)? '<td class="lookupField">' : '<td onclick="lookup(\''.$objectType.'\', \''.$id.'\', \''.$id.'Label\')" class="lookupField">';
	//echo '<td onclick="lookup(\'account\', \''.$id.'\', \''.$id.'Label\')" class="lookupField">';
	echo '<span id="customeridLabel" style="float:left;">';
	echo (!empty($result['RetrieveResult'][$id]['!name']))? $result['RetrieveResult'][$id]['!name'] : '';
	echo '</span>';
	echo '<input type="hidden" id="'.$id.'" name="aEntity['.$id.']" value="';
	echo (!empty($result['RetrieveResult'][$id]['!']))? $result['RetrieveResult'][$id]['!'] : '';
	echo ' />';
	echo '<input type="hidden" id="'.$id.'Type" name="'.$id.'[type]" value="'.$objectType.'" /></td>';
}
?>