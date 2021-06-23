<?php

//The code in this file will be run when the uninstall button for the plugin is clicked.
//All that we have to do is make this uninstall.php file and it will be run automatically on uninstall.
//No need to register any hook. 

//Check if plugin is actually to be uninstalled...
if (!defined('WP_UNINSTALL_PLUGIN'))
	die; //.. if not finish program execution here.

global $wpdb;      
//Remove the "contents" table and all of its data from the WP database.
$wpdb->query("DROP TABLE message_board");