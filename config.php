<?php
/**
 * Database Backup Script
 *
 * @package		ClearMedia
 * @author		Clear Media UK Ltd Dev Team
 * @copyright	Copyright (c) 2008 Clear Media UK.
 * @license
 * @link		http://clearmediawebsites.co.uk
 * @since		Version 1.0
 * @filesource
 */
/**
 * database_backup_script.php
 *
 * Simply re-creates mysqldump and emails the result to your chosen email address
 *
 * @package		ClearMedia
 * @author		Chris Cook
 * @copyright	Copyright (c) 2008, Chris Cook.
 * @since		Version 1.0
 */

/**
 * Database settings
 */
$db_host 		= "localhost"; //the hostname or IP address of the server
$db_user 		= ""; //the username used to access the database
$db_password 	= ""; //the password used to access the database
$db_name 		= ""; //the name of the database you want to backup

/**
 * if you want to send a copy of the backup via email
 * set this to true
 *
 * otherwise set it to false
 */
define("send_mail", true);

/**
 * where to send the email
 *
 * e.g. 
 */
$mail_to = ""; //who to email the backup to

/**
 * If you want to store the zipped copy of the backup file on the server
 * set this to true
 *
 * otherwise set it to false
 */
define("server_copy", FALSE);
