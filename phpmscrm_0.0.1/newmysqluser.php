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

$szPageTitle = 'New MySQL User';

require_once('header.inc.php');
?>
<center>
<br /><br /><br /><br />
<fieldset style="width:330px;">
	<legend>New MySQL User</legend>
	<br />
	<form action="createmysqluser.php" method="post" onsubmit="return checkMandatoryFileds()">
		<table class="formTable">
			<tr>
				<td><label for="szFirstName">First Name <span style="color:red;">*</span>:</label></td>
				<td><input type="text" id="szFirstName" name="szFirstName" value="" /></td>
			</tr>
			<tr>
				<td><label for="szLastName">Last Name <span style="color:red;">*</span>:</label></td>
				<td><input type="text" id="szLastName" name="szLastName" value="" /></td>
			</tr>
			<tr>
				<td><label for="szUserName">Username <span style="color:red;">*</span>:</label></td>
				<td><input type="text" id="szUserName" name="szUserName" value="" /></td>
			</tr>
			<tr>
				<td><label for="szPassword">Password <span style="color:red;">*</span>:</label></td>
				<td><input type="password" id="szPassword" name="szPassword" /></td>
			</tr>
			<tr>
				<td><label for="szEmail">E-mail <span style="color:red;">*</span>:</label></td>
				<td><input type="text" id="szEmail" name="szEmail" value="" /></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="submit" value="Save" /></td>
			</tr>
		</table>
		<input type="hidden" name="aMandatory[]" value="szFirstName" />
		<input type="hidden" name="aMandatory[]" value="szLastName" />
		<input type="hidden" name="aMandatory[]" value="szUserName" />
		<input type="hidden" name="aMandatory[]" value="szPassword" />
		<input type="hidden" name="aMandatory[]" value="szEmail" />
	</form>
</fieldset>
</center>
</body>
</html>
