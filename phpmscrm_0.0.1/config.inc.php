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
 
/**********************************************************
 * Update the parameters below to reflect your architecture
 **********************************************************/
//Mysql Server
define('MYSQL_DB_SERVER', 'mysqlserver');
define('MYSQL_DB_USER', 'mysqluser');
define('MYSQL_DB_PASSWORD', 'mysqlpassword');
define('MYSQL_DB_NAME', 'phpmscrm');

//CRM Server
//Don't forget to activate the CURL extension in PHP to be able to fetch the Web Service
define('CRM_SERVER', 'mscrm');
define('CRM_ORG_NAME', 'ORG');
define('CRM_WSDL', 'http://mscrm/MSCrmServices/2007/CrmServiceWsdl.aspx?uniquename=ORG');

//Active Directory
define('USE_AD_AUTHENTICATION', true);
//Don't forget to activate the LDAP extension in PHP
define('AD_ACCOUNT_SUFFIX', '@mydomain.local');
define('AD_BASE_DN', 'DC=mydomain,DC=local');
define('AD_DOMAIN_CONTROLER', 'dc01.mydomain.local');
?>