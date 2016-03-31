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

$szFirstName = GetHTTPParam('szFirstName', '');
$szLastName = GetHTTPParam('szLastName', '');
$szUserName = GetHTTPParam('szUserName','');
$szPassword = GetHTTPParam('szPassword', '');
$szEmail = GetHTTPParam('szEmail', '');
$aMandatory = GetHTTPParam('aMandatory', array());

$szFormPage = 'newmysqluser.php';

if(($szFirstName == '') || ($szLastName == '') || ($szUserName == '') || ($szPassword == '') || ($szEmail == '')){
	SetSessionParam('szMessage', '1__Mandatory fields missing!');
	LocalRedirect($szFormPage);
}

// check if user exist already
$rsUser = GetListOfUsers('szUserName=' . QuoteEscape($szUserName), '');
$nUser = mysql_num_rows($rsUser);
if ($nUser){
	SetSessionParam('szMessage', '1__Username <b>'.$szUserName.'</b> already exist in the system. Choose another Username');
	LocalRedirect($szFormPage);
}

$aUser = array();
$aUser['szUser'] = $szFirstName.' '.$szLastName;
$aUser['szUserName'] = $szUserName;
$aUser['szPassword'] = md5($szPassword);
$aUser['szEmail'] = $szEmail;

$idUser = 0;
if($idUser = CreateUser($aUser)){
	SetSessionParam('szMessage', '0__User successfully created');
	LocalRedirect($szFormPage);
}else{
	SetSessionParam('szMessage', '1__:There has been an error while creating the new User!');
	LocalRedirect($szFormPage);
}
?>