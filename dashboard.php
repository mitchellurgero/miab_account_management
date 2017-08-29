<?php
session_start();
if(!isset($_SESSION['username'])){
	header("Location: index.php");
}
include('libs/db/db.php');
include('config.php');
$data = new JSONDatabase($config['db'], $config['db_location']);
?>
<html>
	<?php include('head.php'); ?>
	<!-- Dashboard show -->
	<body>
		<div class="container-fluid">
			<nav class="navbar navbar-inverse">
			  <div class="container-fluid">
			    <div class="navbar-header">
			      <a class="navbar-brand" href="./">Dynamic DNS</a>
			    </div>
			    <ul class="nav navbar-nav">
			    	<li><a href="#">Welcome, <?php echo $_SESSION['username'];?>!</a></li>
			    	<li><a href="logout.php"><i class="fa fa-person"></i> Logout</a></li>
			    </ul>
			  </div>
			</nav>
		</div>
		<div class="container">
			<div class="row">
				<?php
				if(isset($_SESSION['msg'])){
					echo '<div class="alert alert-danger">'.$_SESSION['msg'].'</div>';
					$_SESSION['msg'] = null;
					unset($_SESSION['msg']);
				}
				if(isset($_SESSION['good'])){
					echo '<div class="alert alert-success">'.$_SESSION['good'].'</div>';
					$_SESSION['good'] = null;
					unset($_SESSION['good']);
				}
				?>
				<div class="col-md-6">
					<div class="page-header">
						<h2>Current subdomians</h2>
					</div>
					<table class="table table-striped">
						<thead>
							<tr><th>Name</th><th>IP Address</th><th>Options</th></tr>
						</thead>
						<tbody>
							<?php
							$domains = $data->select("subdomains", "username", $_SESSION['username']);
							if(count($domains) < 1){
								echo "<tr><td><b>You have no domains! Please add one to the right -></b></td></tr>\r\n";
							} else {
								foreach($domains as $d){
									echo "<tr><td>".$d['name']."</td><td>".$d['ip'].'</td><td><form action="api.php" method="POST"><input type="hidden" name="t" value="DELETE"><button class="btn btn-sm btn-danger" name="name" value="'.explode(".",$d['name'])[0].'">Delete</button></form></td></tr>'."\r\n";
								}
							}

							?>
						</tbody>
					</table>
				</div>
				<div class="col-md-6">
					<div class="row">
						<div class="page-header">
							<h2>Account Options</h2>
						</div>
						<form action="api.php" method="POST">
							<input type="hidden" name="t" value="PUT">
							<div class="input-group">
	        					<input class="form-control" type="text" name="name" placeholder="name (Max 12. Char.)" maxlength="12"><span class="input-group-addon">@<?php echo $config['dns_domain'];?></span><br>
	        				</div><br>
	        					<input class="form-control" type="text" name="ip" placeholder="<?php echo $_SERVER['REMOTE_ADDR']; ?>"><br>
	        					<button class="btn btn-primary pull-right" type="submit" name="submit">Add Domain</button>
						</form>
					</div>
					<div class="row">
						<div class="page-header">
							<h3>Change Password</h3>
						</div>
						<form action="api.php" method="POST">
							<input type="hidden" name="t" value="password">
							<div class="input-group">
	        					<input class="form-control" type="password" name="old" placeholder="Old Password">
	        				</div><br>
	        					<input class="form-control" type="password" name="password1" placeholder="New Password"><br>
	        					<input class="form-control" type="password" name="password2" placeholder="New Password (Repeat)"><br>
	        					<button class="btn btn-primary pull-right" type="submit" name="submit">Submit</button>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php include('foot.php'); ?>
	</body>
	
</html>