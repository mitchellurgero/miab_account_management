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
			      <a class="navbar-brand" href="aliases.php">Mail-In-A-Box Account Management</a>
			    </div>
			    <ul class="nav navbar-nav">
			    	<li><a href="#">Welcome, <?php echo $_SESSION['username'];?>!</a></li>
			    	<li><a href="dashboard.php"><i class="fa fa-person"></i> Dashboard</a></li>
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
				<div class="col-md-8">
					<div class="page-header">
						<h2>Current Aliases</h2>
					</div>
					<table class="table table-striped">
						<thead>
							<tr><th>Alias</th><th>Sends to</th><th>Options</th></tr>
						</thead>
						<tbody>
							<?php
							$aliases = getAliases();
							foreach($aliases as $alias){
								$email = $alias['address'];
								$display = $alias['address_display'];
								$fwdto = $alias['forwards_to'];
								echo '<tr><td>'.$email.'</td><td><ul>';
								foreach($fwdto as $fwd){
									echo '<li>'.$fwd.'</li>';
								}
								echo '</ul></td><td>';
								if(!$alias['required']){
									echo '
								<form action="api.php" method="POST">
									<input type="hidden" name="t" value="delAlias">
									<input type="hidden" name="address" value="'.$email.'">
									<button class="btn btn-danger btn-small" type="submit">Delete Alias</button>
								</form>
								';
								} else {
									echo 'Cannot delete admin alias.';
								}
								
								echo '</td></tr>';
							}
							?>
						</tbody>
					</table>
				</div>
				<div class="col-md-4">
					<div class="page-header">
						<h2>New Alias</h2>
					</div>
					<form action="api.php" method="POST">
						<input type="hidden" name="t" value="newAlias">
						<p>Alias Name:</p>
						<div class="input-group">
	        				<input class="form-control" type="text" name="address" placeholder="Alias (Max 15. Char.)" maxlength="15"><span class="input-group-addon">@<?php echo $config['domain'];?></span><br>
	        			</div><br>
	        			<p>Forwards To:</p>
	        			<textarea class="" rows="4" style="width:100%" name="forwards"></textarea><br><br>
	        			<button class="btn btn-primary pull-right" type="submit">Add Alias</button>
					</form>
				</div>
			</div>
		</div>
		<?php include('foot.php'); ?>
	</body>
	
</html>