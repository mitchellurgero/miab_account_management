<?php
session_start();
if(!isset($_SESSION['username'])){
	header("Location: index.php");
}
include('libs/db/db.php');
include('config.php');
include('functions.php');
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
			      <a class="navbar-brand" href="./">Mail-In-A-Box Account Management</a>
			    </div>
			    <ul class="nav navbar-nav">
			    	<li><a href="#">Welcome, <?php echo $_SESSION['username'];?>!</a></li>
			    	<li><a href="aliases.php"><i class="fa fa-person"></i> Aliases</a></li>
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
						<h2>Current Users</h2>
					</div>
					<table class="table table-striped">
						<thead>
							<tr><th>Name</th><th>Account Type</th><th>Options</th></tr>
						</thead>
						<tbody>
							<?php
						$users = getUsers();
						$aliases = getAliases();
						$noDel = false;
						if(count($users) == 1){
							$noDel = true;
						}
						foreach($users as $user){
							$uType = "User";
							$userAliases = '';
							foreach($aliases as $alias){
								if(in_array($user['email'],$alias['forwards_to'])){
									$userAliases .= "<li>".$alias['address']."</li>";
								}
							}
							$delForm = '<form action="api.php" method="POST">
								<input type="hidden" name="t" value="archive">
								<input type="hidden" name="email" value="'.$user['email'].'">
								<button class="btn btn-danger btn-sm" type="submit">Delete User</button>
							</form>';
							if(!empty($user['privileges'])){
								$uType = "Admin";
							}
							echo '<tr><td>'.$user['email'].'<br>
							<ul>
							'.$userAliases.'
							</ul>
							</td><td>'.$uType.'</td><td>';if(!$noDel){ echo $delForm; }echo '</td></tr>';
						}
							?>
						</tbody>
					</table>
				</div>
				<div class="col-md-6">
					<div class="row">
						<div class="page-header">
							<h2>Make New User</h2>
						</div>
						<form action="api.php" method="POST">
							<input type="hidden" name="t" value="new">
							<div class="input-group">
	        					<input class="form-control" type="text" name="userName" placeholder="Username (Max 15. Char.)" maxlength="15"><span class="input-group-addon">@<?php echo $config['domain'];?></span><br>
	        				</div><br>
	        					<input class="form-control" type="password" name="userPass" placeholder="Su73rSt40ngP@ssW0rD"><br>
	        					<button class="btn btn-primary pull-right" type="submit" name="submit">Add Account</button>
						</form>
					</div>
					<div class="row">
						<div class="page-header">
							<h3>Change Dashboard Password</h3>
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