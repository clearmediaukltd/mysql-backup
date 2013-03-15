mysql-backup
============

This is just a simple script I put together to backup a MySQL database. It's mainly designed to be run as a cron job, but can be run manually too.

How To Use
==========

Edit the config.php file, adding your database connection details. Decide whether you want the backups emailed to you, or stored on the server, or both.

Then simply run the database_backup_script.php either through your web browser or via a cron job.

Here's an example cron command: 

/path/to/your/folder/;/usr/bin/php -q database_backup_script.php