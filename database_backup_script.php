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

require("Zipfile.php"); //a class used for zipping
require("config.php");	//the configuration settings

$fileatt_type = 'application/octet-stream'; //the mime type of the emailed file (DO NOT CHANGE)
$table_list = array();

echo "--- Process started! ---<br /><br />";

//Check the directory permissions allow us to write files
if(substr(sprintf('%o', fileperms(getcwd())), -4) != '0777') {
	echo "<strong>Directory Permissions Should be 777</strong>!<br />";	
} else {
	echo "Successfully Checked Directory Permissions<br />";
}

/**
 * Script kicks off here
 */ 
$connection = mysql_connect("$db_host", "$db_user", "$db_password");
mysql_select_db($db_name, $connection);

//find all the tables in the database
$sql = "SHOW TABLES FROM $db_name";
$result = mysql_query($sql);

$contents = "Database Backup: $db_name \n-- Created: " . date('M j, Y') . " at " . date('h:i A') . "\n\n";

while($tables = mysql_fetch_array($result)) {
    $table_list[] = $tables[0];
}

if(sizeof($table_list) > 0) {
	echo "Successfully Built Table List<br />";
}

//Create the backup file
foreach($table_list as $table) {

    $row = mysql_fetch_assoc(mysql_query('SHOW CREATE TABLE ' . $table));
    $contents .= $row["Create Table"] . ";\n\n";
    $sql = 'SELECT * FROM ' . $table;
    $result = mysql_query($sql);
    $columns = explode(',', $row["Create Table"]);
    $i = 0;
	
    while($records = mysql_fetch_array($result)) {
	
        $contents .= "INSERT INTO " . $table . " VALUES (";
		
        for($i = 0; $i <= count($records)/2; $i++) {
		
            if($i < count($records)/2-1) {
			
				//if we have several records we need to add commas
                if (strstr($columns[$i], "varchar") || strstr($columns[$i], "text")) {
                    $contents .= "'".$records[$i]."',";
                } else {
                    $contents .= $records[$i].",";
                }
				
            } else {
			
				//if we only have one, we don't want the commas
                if (strstr($columns[$i], "varchar") || strstr($columns[$i], "text")) {
                    $contents .= "'" . $records[$i] . "'";
                } else {
                    $contents .= $records[$i] . "";
                }
			
            }
        }
        $contents .= ");\n";
        $i++;
    }
    $contents .= "\n";
}

//Write out the backup file
$file = 'DB_Backup_' . $db_name . '_' . date('Y-m-d') . '.sql';
$handle = fopen($file, 'w');
fwrite($handle, $contents);
fclose($handle);

/**
 * create a zipped version of our backup
 */
$zipfile = new Zipfile();
$filedata = implode("", file($file));
$zipfile->add_file($filedata, $file);
$data = $zipfile->file();

//remove the original backup
unlink($file);

//need this here as it's used in our email
$file = 'DB_Backup_' . $db_name . '_' . date('Y-m-d') . '.zip';

//if you don't want to store a copy of the backup on the server
//we need to delete the file
if(server_copy === true) {
	/**
	 * write out the zipped file
	 */
	$handle = fopen($file, 'w');
	$result = fwrite($handle, $data);
	fclose($handle);
	
	if($result != false) {
		echo "Successfully wrote Zipped Backup File<br />";
	}
} else {
	echo "Skipped writing Zipped Backup File<br />";
}

// Generate a boundary string 
$semi_rand = md5(time()); 
$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 

$headers = "From: $mail_to"; 
	
// Add the headers for a file attachment 
$headers .= "\nMIME-Version: 1.0\n" . 
				 "Content-Type: multipart/mixed;\n" . 
				 " boundary=\"{$mime_boundary}\"";	

// Add a multipart boundary above the plain message 
$message = "This is a multi-part message in MIME format.\n\n" . 
			"--{$mime_boundary}\n" . 
			"Content-Type: text/html; charset=\"iso-8859-1\"\n" . 
			"Content-Transfer-Encoding: 7bit\n\n";								

$data = chunk_split(base64_encode($data));

// Add file attachment to the message 
$message .= "--{$mime_boundary}\n" . 
			 "Content-Type: {$fileatt_type};\n" . 
			 " name=\"{$file}\"\n" . 
			 "Content-Disposition: attachment;\n" . 
			 " filename=\"{$file}\"\n" . 
			 "Content-Transfer-Encoding: base64\n\n" . 
			 $data . "\n\n" . 
			 "--{$mime_boundary}--\n";

//Send the zip file via email
if(send_mail) {						
	$result = mail($mail_to, 'Database Backup', $message, $headers);
	if($result) {
		echo "Sent email<br />";
	} else {
		echo "Could not send email<br />";
	}
} else {
	echo "Skipped sending email<br />";
}

echo "<br />--- Process completed! ---<br /><br />";
?> 