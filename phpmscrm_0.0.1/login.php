<?php
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

session_unset();

$szfRedirect = GetHTTPParam('fRedirect');
//If DOMAINE\ is used remove it before continuing
$szUserName = str_ireplace ( 'EUROPE\\' , '' , GetHTTPParam('szUserName'));
$szfPassword = GetHTTPParam('szPassword');

// get user
$rsUser = GetListOfUsers('szUserName=' . QuoteEscape($szUserName), '');
$nUser = mysql_num_rows($rsUser);
if ($nUser < 1)
{
	// no match
	SetSessionParam('szMessage', '1__The system could not authenticate your username and password.');
	LocalRedirect('index.php?szUserName=' . urlencode($szUserName));
}

// retrieve user record
$rUser = mysql_fetch_array($rsUser);
if (!$rUser)
{
	// database error
	SetSessionParam('szMessage', '1__The system could not authenticate your username and password.');
	LocalRedirect('index.php?szUserName=' . urlencode($szUserName));
}

// check User Name
if (strcasecmp($szUserName, $rUser['szUserName']) != 0)
{
	SetSessionParam('szMessage', '1__The system could not authenticate your username and password.');
	LocalRedirect('index.php?szUserName=' . urlencode($szUserName));
}

//authenticate the user
$aUser = array();
if(USE_AD_AUTHENTICATION){
	//Authentify user against Active Directory
	//include the class and create a connection
	include("libraries/adLDAP/adLDAP.php");
	try {
		$options=array('account_suffix' => AD_ACCOUNT_SUFFIX, 'base_dn' => AD_BASE_DN, 'domain_controllers' => array (AD_DOMAIN_CONTROLER));
		$adldap = new adLDAP($options);
	}
	catch (adLDAPException $e) {
		SetSessionParam('szMessage', '1__'.$e);
		LocalRedirect('index.php?szUserName=' . urlencode($szUserName));  
	}
	if (!$adldap -> authenticate($szUserName,$szfPassword)){
		SetSessionParam('szMessage', '1__The system could not authenticate your username and password.');
		LocalRedirect('index.php?szUserName=' . urlencode($szUserName));
	}
	
	//update User infos from AD
	$userADInfos=$adldap->user_info($szUserName);
	$aUser['idUser'] = $rUser['idUser'];
	$aUser['szUser'] = $userADInfos[0]['displayname'][0];
	$aUser['szUserName'] = $szUserName;
	//Update MYSQL User Infos
	UpdateUser($rUser['idUser'], $aUser);

	//Set password for the session to be used in the CRM communication
	$aUser['szPassword'] = $szfPassword;
}else{
	//Authentify user against MySQL Database
	$aUser['idUser'] = $rUser['idUser'];
	$aUser['szUser'] = $rUser['szUser'];
	$aUser['szUserName'] = $szUserName;
	$aUser['szPassword'] = $szfPassword;
	// check password again
	if (strcmp(md5($szfPassword), $rUser['szPassword']) != 0)
	{
		SetSessionParam('szMessage', '1__The system could not authenticate your username and password.');
		LocalRedirect('index.php?szUserName=' . urlencode($szUserName));
	}
}

// success: PHP/MYSQL log user on
LogUser($aUser);

//Update Current User Infos
$crmSoap = new DynamicsCRM();
$currentUser = $crmSoap->getCurrentUserInfo();	
$aUser = array();
$aUser['crmUserId'] = $currentUser['Response']['UserId'];
$aUser['crmBusinessUnitId'] = $currentUser['Response']['BusinessUnitId'];
$aUser['crmOrganizationId'] = $currentUser['Response']['OrganizationId'];
UpdateUser(GetSessionParam('idUser'), $aUser);
SetSessionParam('crmUserId',$currentUser['Response']['UserId']);

if ($szfRedirect == '')
	$szfRedirect = 'listactivities.php';
	
LocalRedirect($szfRedirect);
?>