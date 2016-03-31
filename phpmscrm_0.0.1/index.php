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

$szRedirect = GetSessionParam('szRedirect', '');

if(IsLogged()){
	if ($szRedirect == '')
		$szRedirect = 'listactivities.php';
		
	LocalRedirect($szRedirect);
}else{
	$szUserName = GetHTTPParam('szUserName');
	require_once('header.inc.php');
	?>
	<center>
		<br /><br /><br /><br />
		<div style="width:370px;">
			<fieldset class="clsNoPrint" style="margin-right:20px;margin-bottom:20px;">
				<legend style="font-weight:bold;">Login:</legend>
				<br />
				<form action="login.php" method="post" onsubmit="return checkLoginPassword()">
					<table class="formTable" style="width:100%;">
						<tr>
							<td style="text-align:right;"><label for="szUserName">Username:</label></td>
							<td><input type="text" id="szUserName" name="szUserName" value="<?=$szUserName?>" /></td>
						</tr>
						<tr>
							<td style="text-align:right;"><label for="szPassword">Password:</label></td>
							<td><input type="password" id="szPassword" name="szPassword" /></td>
						</tr>
						<tr>
							<td></td>
							<td style="text-align:left;"><input type="submit" value="Connexion" /></td>
						</tr>
					</table>
					<input type="hidden" name="fRedirect" value="<?= $szRedirect ?>" />
					<br />
				</form>
			</fieldset>
		</div>
	</center>
	</body>
	</html>
<?
}
?>
