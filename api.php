<?php
session_start();
include('libs/db/db.php');
include('config.php');
$db = new JSONDatabase($config['db'], $config['db_location']);
if(!isset($_SESSION['username'])){
	$_SESSION['msg'] = "You must be logged in to use this website!";
	header("Location: index.php");
	die();
}
if(isset($_POST['t'])){
	if($_POST['t'] == "password"){
		$account = $db->select("accounts", "username", $_SESSION['username']);
		if(count($account) < 1){
			header("Location: index.php");
			die();
		} else {
			$account = reset($account);
			if($_POST['password1'] != $_POST['password2']){
				$_SESSION['msg'] = "ERROR! New password does NOT MATCH.";
				header("Location: dashboard.php");
				die();
			}
			if(password_verify($_POST['old'],$account['password'])){ //Verify old password before changing it.
				$user = $account;
				$user['password'] = password_hash($_POST['password1'], PASSWORD_DEFAULT);
				$db->insert("accounts", json_encode($user), $user['row_id']);
				$_SESSION['good'] = "SUCCESS! Your password has been changed!";
				header("Location: dashboard.php");
				die();
			} else {
				$_SESSION['msg'] = "ERROR! OLD password does NOT MATCH current password.";
				header("Location: dashboard.php");
				die();
			}
			
		}
	} else {
		$ret = curl($_POST);
		if($ret === false){
			$_SESSION['msg'] = "An error has occured, please try again.";
		} elseif(strpos($ret, "SUCCESS") !== false) {
			$_SESSION['good'] = $ret;
		} elseif(strpos($ret, "ERROR") !== false) {
			$_SESSION['msg'] = $ret;
		} else{
			$_SESSION['msg'] = $ret;
		}
		header("Location: dashboard.php");
		die();	
	}
	
} else {
	$_SESSION['msg'] = "You must specify a variable!";
	header("Location: dashboard.php");
	die();
}

function curl($data = array()){
	global $config,$db;
	//first make sure this shit is safe.
	foreach($data as $key=>$d){
		$data[$key] = clean($d);
	}
	$url = "https://".$config['miab_server']."/admin/dns/custom/";
	$ret = "";
	$username = $config['admin'];
	$password = $config['pass'];
	$context = stream_context_create(array(
		    'http' => array(
		        'header'  => "Authorization: Basic " . base64_encode("$username:$password")
		    )
		));
	$put = $data['t'];
	
	switch($put){
		case "PUT":
			$ip = $data['ip'];
			if($data['ip'] == ""){
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			if($data['name'] == ""){
				return "ERROR: You must specify a name!";
			}
			$data['name'] = substr($data['name'],0,12); //Force name length of 12.
			$domain = $data['name'].".".$config['dns_domain'];
			//Check if domain exists.
			$exists = json_decode(file_get_contents($url."$domain",false,$context), TRUE);
			if(!empty($exists)){
				return "ERROR: That name is already in use! Please try a different name!";
			}
			$handle = curl_init($url."$domain");
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        	curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($handle, CURLOPT_POSTFIELDS, $ip);//str_replace("=",http_build_query(array($ip=>""))));
			curl_setopt($handle, CURLOPT_USERPWD, "$username:$password");
			curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($handle, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
			curl_setopt($handle, CURLOPT_TIMEOUT, 10);
			$ret = curl_exec($handle);
			curl_close($handle);
			if(strpos($ret, "does not appear") !== false){
				return "ERROR: That IP Address is not valid! Please try again!";
			}elseif(strpos($ret,"updated DNS") !== false){
				$db->insert("subdomains",json_encode(array("username"=>$_SESSION['username'], "ip"=>$ip,"name"=>$domain)));
				return "SUCCESS: Added '$domain' subdomain to your account! Please give at least 24 hours for it to propogate properly.";
			}
			return $ret;
			break;
		case "DELETE":
			$domain = $data['name'].".".$config['dns_domain'];
			//Check if domain exists.
			$exists = json_decode(file_get_contents($url."$domain",false,$context), TRUE);
			if(empty($exists)){
				return "ERROR: That name does not exists!";
			}
			//Check if user owns domain:
			$rows = $db->select("subdomains","username",$_SESSION['username']);
			$row = '';
			if(!empty($rows)){
				foreach($rows as $r){
					if(trim($r['name']) == trim($domain)){
						$row = $r['row_id'];
						break; //Break out of foreach because we found our domain.
					}
				}
			}
			if($row == ""){
				return "ERROR: You do not own that domain.";
			}
			$handle = curl_init($url."$domain");
			curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        	curl_setopt($handle, CURLOPT_CUSTOMREQUEST, "DELETE");
			curl_setopt($handle, CURLOPT_USERPWD, "$username:$password");
			curl_setopt($handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($handle, CURLOPT_TIMEOUT, 10);
			$ret = curl_exec($handle);
			curl_close($handle);
			if(strpos($ret,"updated DNS") !== false){
				$db->insert("subdomains",json_encode(array("username"=>$_SESSION['username']."-".generateRandomString(128))),$row); //Not deleting data, just "nulling" the username so it cannot be seen, it is however deleted from MIAB.
				return "SUCCESS: Deleted '$domain' from your account! Please give at least 24 hours for it to propogate properly.";
			} else {
				return "ERROR: $ret";
			}
			return $ret;
			break;
		default:
			return "ERROR: There was an unknown error!";
	}
	
}
function clean($string) {
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
   $string = str_replace(".","",$string);
   $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
   return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
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
?>