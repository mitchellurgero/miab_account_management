<?php
echo "<pre>";
include('libs/db/db.php');
echo "Loaded DB Class..\r\n";
include('config.php');

$data = new JSONDatabase($config['db'], $config['db_location']);
if($data->check_table('accounts') !== true){
	$data->create_table('accounts');
	$p = password_hash("password", PASSWORD_DEFAULT);
	$d = array("username"=>"admin", "password"=>"$p");
	$data->insert("accounts", json_encode($d), 0);
	echo "Created account 'admin' with password 'password'. Change this on first login!\r\n";
	//Create empty DNS DB.
	$data->create_table("subdomains");
} else {
	echo "Either the install was completed before, or an error occured.\r\n";
}
echo "</pre>";
?>