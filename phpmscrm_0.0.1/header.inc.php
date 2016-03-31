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
 ?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head><meta http-equiv="content-type" content="text/html;charset=ISO-8859-1" /><title>PHP MSCRM <?=(!empty($szPageTitle))? '- '.$szPageTitle : ''?></title><link rel="stylesheet" type="text/css" href="style.css" /><script type="text/javascript" src="script.js"></script><!--IE7  fix to hide right side scroll--><!--[if IE 7]><!--><style type="text/css">html {overflow:auto;}</style><!--<![endif]--></head>
<body>
<table class="clsNoPrint" style="text-align:left;width:100%;background-color:#8BAFE4;color:#fff;-moz-box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.4);-webkit-box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.4);box-shadow: 0px 3px 5px rgba(0, 0, 0, 0.4);">
	<tr>
		<td style="padding:6px;width:25%;white-space:nowrap;">
		<?
		if(IsLogged()){
		?>
			<div class="menu">
				<ul>
					<li style="background:#4986d9;"><a style="width:90px;text-align:center;" href="#">Menu&nbsp;<small>&#9660;</small><!--[if IE 7]><!--></a><!--<![endif]--><!--[if lte IE 6]><table><tr><td><![endif]-->
						<ul><!--[if lte IE 6.5]><li><iframe></iframe></li><![endif]-->
							<li><a href="listactivities.php">Activities</a></li><!--[if lte IE 6.5]><li><iframe></iframe></li><![endif]-->
							<li><a href="listaccounts.php">Accounts</a></li><!--[if lte IE 6.5]><li><iframe></iframe></li><![endif]-->
							<li><a href="listcontacts.php">Contacts</a></li><!--[if lte IE 6.5]><li><iframe></iframe></li><![endif]-->
							<li><a href="listopportunities.php">Opportunities</a></li><!--[if lte IE 6.5]><li><iframe></iframe></li><![endif]-->
							<li><a href="listleads.php">Leads</a></li><!--[if lte IE 6.5]><li><iframe></iframe></li><![endif]-->
							<li><a href="listusers.php">Users</a></li>
						</ul><!--[if lte IE 6]></td></tr></table></a><![endif]-->
					</li>
				</ul>
			</div>
		<?
		}
		?>
		</td>
		<td style="text-align:center;width:50%;white-space:nowrap;font-size:16px;"><b>PHP MSCRM</b></td>
		<td style="padding:6px;text-align:right;width:25%;white-space:nowrap;">
		<?
		if(IsLogged())
			echo GetSessionParam('szUser').' | <a href="logout.php">Logout</a>';
		?>
		</td>
	</tr>
</table>
<?
$szMessageList = explode('__',GetSessionParam('szMessage'));
$szMessage = (isset($szMessageList['1']))? $szMessageList['1'] : '';
UnsetSessionParam('szMessage');

if($szMessage != ''){
?>
<div style="margin-top:5px;color:<?=($szMessageList['0'] == 1)? 'red' : 'green' ?>;text-align:center;font-size:12px;"><?= ($szMessage != '')? $szMessage : '&nbsp;' ?></div>
<?
}
?>