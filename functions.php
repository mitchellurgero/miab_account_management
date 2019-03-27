<?php
$username = $config['admin'];
$password = $config['pass'];
function archiveUser($u){
	global $config,$username,$password;
	$fields_string = "";
	$url = "https://".$config['hostname']."/admin/mail/users/remove";
	$fields = array(
		"email"    => urlencode($u)
		);
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	$data = curl_exec($ch);
	curl_close($ch);
	
	if($data){
		return true;
	} else {
		return false;
	}
	
}
function makeNewUser($u, $p){
	global $config,$username,$password;
	$fields_string = "";
	$url = "https://".$config['hostname']."/admin/mail/users/add";
	$fields = array(
		"email"    => urlencode($u),
		"password" => urlencode($p)
		);
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	$data = curl_exec($ch);
	curl_close($ch);
	if(curl_errno($ch)){
		error_log($data);
		return false;
	} else {
		return true;
	}
}
function getAliases(){
	global $config,$username,$password;
	$url = "https://".$config['hostname']."/admin/mail/aliases?format=json";
	$json = "";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	$data = curl_exec($ch);
	curl_close($ch);
	if(json_decode($data)){
		$json = json_decode($data,true);
		$aliases = $json;
		foreach($aliases as $domain){
			if($domain['domain'] == $config['domain']){
				return $domain['aliases'];
			}
		}
	} else {
		return 0;
	}
	
	return 0;
}
function addAlias($u, $alias){
	global $config,$username,$password;
	$array = preg_split("/\r\n|\n|\r/", $alias);
	$fields_string = "";
	$url = "https://".$config['hostname']."/admin/mail/aliases/add";
	$fields = array(
		"address"    => urlencode($u."@".$config['domain']),
		"forwards_to" => urlencode(implode(",",$array))
		);
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	$data = curl_exec($ch);
	error_log($data);
	if(curl_errno($ch)){
		error_log($data);
		curl_close($ch);
		return false;
	} else {
		curl_close($ch);
		return true;
	}
	
}
function delAlias($u){
	global $config,$username,$password;
	$fields_string = "";
	$url = "https://".$config['hostname']."/admin/mail/aliases/remove";
	$fields = array(
		"address"    => urlencode($u)
		);
	foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
	rtrim($fields_string, '&');
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	$data = curl_exec($ch);
	if($data){
		curl_close($ch);
		return true;
	} else {
		curl_close($ch);
		return false;
	}
}
function getUsers(){
	global $config,$username,$password;
	$url = "https://".$config['hostname']."/admin/mail/users?format=json";
	$json = "";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	$data = curl_exec($ch);
	if(json_decode($data)){
		$json = json_decode($data,true);
		$domains = $json;
		foreach($domains as $domain){
			if($domain['domain'] == $config['domain']){
				curl_close($ch);
				return $domain['users'];
			}
		}
	} else {
		curl_close($ch);
		return 0;
	}
	curl_close($ch);
	return 0;
}
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}