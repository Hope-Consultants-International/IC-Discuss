Prerequisites
=============

PHP 5.4+ (with pdo & pdo_mysql)
MySQL 5.1+
Apache 2+ [1]


Installation
============

To install IC-Discuss, unpack the contents of this archive to a folder on you webserver.

Create a new database and user for ic-discuss with full rights.

Copy of includes/config.ini.example to includes/config.ini and fill in the database user.

Navigate to update.php with a browser. This should initialize the database.


Updates
=======

Make a copy of the database.

Unpack the contents of this archive to the same folder you used before.

Check includes/config.ini.example for new configuration parameters.

Navigate to update.php with a browser. This will update the database.


Notes
=====

[1]: You can use a webserver other than Apache, but you will have to convert the .htaccess files found in includes/, doc/ and templates/.  
     For access control, IC-Discuss relies on the precense of ther REMOTE_USER variable.