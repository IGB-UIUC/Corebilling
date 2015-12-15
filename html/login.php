<?php
	require_once 'includes/initializer.php';
	
	if(isset($_POST['login'])) {
		$username = trim(rtrim($_POST['user_name']));
		$password = $_POST['password'];

		$loginsuccess = $authenticate->Login($username,$password);
		if( $loginsuccess ){
			header('Location: index.php');
		} else {
			$message = html::error_message('Incorrect username or password.');
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
		
		<script src='js/jquery/jquery-1.11.1.min.js'></script>
		<script src='js/bootstrap/bootstrap.min.js'></script>
			
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<title>IGB Instrument Usage Page</title>
	</head>
	<body>
		<nav class="navbar navbar-inverse navbar-static-top">
			<div class="container-fluid">
				<div class="navbar-header">
					<div class="navbar-brand">
						<?php echo PAGE_TITLE; ?>
					</div>
				</div>
			</div>
		</nav>
	
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-3 col-md-offset-4">
					<form role="form" class="form-signin" action="./login.php" method="POST"  style="margin-bottom:1em">
						<div class="form-group">
							<label for="username">Username: </label>
							<div class="input-group">
								<input class='form-control' type='text' name='user_name' id="username" tabindex='1' placeholder='Username' value='<?php if (isset($username)) { echo $username; } ?>'> 
								<span class="input-group-addon"><span class='glyphicon glyphicon-user'></span></span>
							</div>
						</div>
						<div class="form-group">
							<label>Password: </label>
							<div class="input-group">
								<input class='form-control' type='password' name='password' placeholder='Password' tabindex='2'>
								<span class="input-group-addon"><span class='glyphicon glyphicon-lock'></span></span>
							</div>
						</div>
						<button type='submit' name='login' class='btn btn-primary'>Login</button>
					</form>
					<?php if (isset($message)) { 
						echo $message;
					} ?>
				</div>
			</div>
		</div>
	</body>
</html>