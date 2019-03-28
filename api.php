<?php
session_start();
include('libs/db/db.php');
include('config.php');
include('functions.php');
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
		// Manage User account
		switch(strtolower($_POST['t'])){
			case "new":
				if(isset($_POST['userName'],$_POST['userPass'])){
					if(makeNewUser($_POST['userName']."@".$config['domain'], $_POST['userPass'])){
						$_SESSION['good'] = "SUCCESS! The New user account has been created!";
					} else {
						$_SESSION['msg'] = "ERROR! New user creation failed! Please check logs for details.";
					}
				} else {
					$_SESSION['msg'] = "ERROR! New user creation failed! Please check logs for details.";
				}
				header("Location: dashboard.php");
				break;
			case "restore":
				if(isset($_POST['userName'])){
					$nPass = generateRandomString(14);
					$nUser = explode("@", $_POST['userName']);
					if(makeNewUser($nUser[0]."@".$config['domain'], $nPass)){
						$_SESSION['good'] = "SUCCESS! The user account has been restored with the password of <code>$nPass</code>";
					} else {
						$_SESSION['msg'] = "ERROR! User restore failed! Please check logs for details.";
					}
				} else {
					$_SESSION['msg'] = "ERROR! User restore failed! Please check logs for details.";
				}
				header("Location: dashboard.php");
				break;
			case "archive":
				if(isset($_POST['email'])){
					if(archiveUser($_POST['email'])){
						$_SESSION['good'] = "SUCCESS! The account was deleted!";
					} else {
						$_SESSION['msg'] = "ERROR! Unable to archive user, please check logs for details.";
					}
				} else {
					$_SESSION['msg'] = "ERROR! Unable to archive user, please check logs for details.";
				}
				header("Location: dashboard.php");
				break;
			case "delalias":
				if(isset($_POST['address'])){
					if(delAlias($_POST['address'])){
						$_SESSION['good'] = "SUCCESS! The alias was deleted!";
					} else {
						$_SESSION['msg'] = "ERROR! Unable to delete alias, please check logs for details.";
					}
				}
				header("Location: aliases.php");
				break;
			case "newalias":
				if(isset($_POST['address'], $_POST['forwards'])){
					if(addAlias($_POST['address'],$_POST['forwards'])){
						$_SESSION['good'] = "SUCCESS! The alias was added!";
					} else {
						$_SESSION['msg'] = "ERROR! Unable to add alias, please check logs for details.";
					}
				}
				header("Location: aliases.php");
				break;
			
			default:
				$_SESSION['msg'] = "ERROR! API Failure.";
				header("Location: dashboard.php");
				break;
		}
		
	}
	
} else {
	$_SESSION['msg'] = "You must specify a variable!";
	header("Location: dashboard.php");
	die();
}
function clean($string) {
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
   $string = str_replace(".","",$string);
   $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
   return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}
?>