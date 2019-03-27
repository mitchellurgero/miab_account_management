<?php
session_start();
include('libs/db/db.php');
include('config.php');
//die("DISABLED");
$data = new JSONDatabase($config['db'], $config['db_location']);
if(isset($_SESSION['username'])){
	//We are logged in, redirect to dashboard.
	header("Location: dashboard.php");
	die();
}
if(isset($_POST['username']) && isset($_POST['t'])){
	if($_POST['t'] == "login"){
		//Attempt to login:
		$user = $data->select("accounts", "username", $_POST['username']);
		if(count($user) < 1){
			$_SESSION['msg'] = "The username or password you entered was incorrect, please try again..";
			header("Location: index.php");
			die();
		}
		$user = reset($user);
		$pass = $user['password'];
		if(password_verify($_POST['password'], $pass)){
			$_SESSION['username'] = $user['username'];
			header("Location:dashboard.php");
			die();
		} else {
			//Password was incorrect
			$_SESSION['msg'] = "The username or password you entered was incorrect, please try again.";
		}
	} elseif($config['registration'] == true && $_POST['t'] == "register"){
		$user = $data->select("accounts", "username", $_POST['username']);
		if(count($user) < 1){
			//User not taken, let's create an account.
			if($_POST['password'] == $_POST['confirm-password']){
				//Password matches
				$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
				$username = substr(clean($_POST['username']),0,12);
				$userData = array("username"=>"$username","password"=>"$password");
				if($data->insert("accounts", json_encode($userData))){
					$_SESSION['good'] = "SUCCESS! You may now login with the username '$username'";
				} else {
					$_SESSION['msg'] = "There was an error during registration, please try again later, or contact the site administrator.";
				}
			}else{
				$_SESSION['msg'] = "ERROR! The password you entered did not match, please try again.";
			}
		} else {
			$_SESSION['msg'] = "The username you entered is taken. Please try again.";
		}
	}elseif($config['registration'] == false && $_POST['t'] == "register"){
			$_SESSION['msg'] = "Registration is currently disabled. Please contact the Administrator.";
	}
	
}
//Not logged in, let's give them a login page
?>
<html>
	<?php include('head.php'); ?>
	<body>
		<div class="container-fluid">
			<nav class="navbar navbar-inverse">
			  <div class="container-fluid">
			    <div class="navbar-header">
			      <a class="navbar-brand" href="./">Mail-In-A-Box Account Management</a>
			    </div>
			    <ul class="nav navbar-nav">
			    </ul>
			  </div>
			</nav>
		</div>
		<div class="container">
			<br><br>
	    	<div class="row">
				<div class="col-md-6 col-md-offset-3">
					<div class="panel panel-login">
						<div class="panel-heading">
							<div class="row">
								<div class="col-xs-6">
									<a href="#" class="active" id="login-form-link">Login</a>
								</div>
								<div class="col-xs-6">
									<a href="#" id="register-form-link">Register</a>
								</div>
							</div>
							<hr>
						</div>
						<div class="panel-body">
							<div class="row">
								<div class="col-lg-12">
									<form id="login-form" action="index.php" method="POST" role="form" style="display: block;">
										<input type="hidden" name="t" value="login">
										<?php
										if(isset($_SESSION['msg'])){
											echo '<div class="alert alert-danger"><strong>ERROR!</strong> '.$_SESSION['msg'].'</div>';
											$_SESSION['msg'] = null;
											unset($_SESSION['msg']);
										}
										if(isset($_SESSION['good'])){
											echo '<div class="alert alert-success">'.$_SESSION['good'].'</div>';
											$_SESSION['good'] = null;
											unset($_SESSION['good']);
										}
										?>
										<div class="form-group">
											<input type="text" name="username" id="username" tabindex="1" class="form-control" placeholder=" Username" value="">
										</div>
										<div class="form-group">
											<input type="password" name="password" id="password" tabindex="2" class="form-control" placeholder=" Password">
										</div>
										<div class="form-group">
											<div class="row">
												<div class="col-sm-6 col-sm-offset-3">
													<input type="submit" name="login-submit" id="login-submit" tabindex="4" class="form-control btn btn-login" value="Log In">
												</div>
											</div>
										</div>
										<!--<div class="form-group">
											<div class="row">
												<div class="col-lg-12">
													<div class="text-center">
														<a href="#" tabindex="5" class="forgot-password">Forgot Password?</a>
													</div>
												</div>
											</div>
										</div>-->
									</form>
									<?php
									if($config['registration']){
										echo ''
										?>
									<form id="register-form" action="index.php" method="POST" role="form" style="display: none;">
										<input type="hidden" name="t" value="register">
										<div class="form-group">
											<input type="text" name="username" id="username" tabindex="1" class="form-control" placeholder=" Username" value="" maxlength="12">
										</div>
										<div class="form-group">
											<input type="password" name="password" id="password" tabindex="2" class="form-control" placeholder=" Password">
										</div>
										<div class="form-group">
											<input type="password" name="confirm-password" id="confirm-password" tabindex="2" class="form-control" placeholder=" Confirm Password">
										</div>
										<div class="form-group">
											<div class="row">
												<div class="col-sm-6 col-sm-offset-3">
													<input type="submit" name="register-submit" id="register-submit" tabindex="4" class="form-control btn btn-register" value="Register Now">
												</div>
											</div>
										</div>
									</form>
										<?php
									} else {
										?>
										<div class="text-center">Registration is currently disabled.</div>
										<?php
									}
									?>
									
								</div>
							</div>
						</div>
					</div>
					
				</div>
			</div>
		</div>
		</div>
		<?php include('foot.php'); ?>
		<script>
			$(function() {

    $('#login-form-link').click(function(e) {
		$("#login-form").delay(100).fadeIn(100);
 		$("#register-form").fadeOut(100);
		$('#register-form-link').removeClass('active');
		$(this).addClass('active');
		e.preventDefault();
	});
	$('#register-form-link').click(function(e) {
		$("#register-form").delay(100).fadeIn(100);
 		$("#login-form").fadeOut(100);
		$('#login-form-link').removeClass('active');
		$(this).addClass('active');
		e.preventDefault();
	});

});
		</script>
	</body>
</html>
<?php
function clean($string) {
   $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
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
