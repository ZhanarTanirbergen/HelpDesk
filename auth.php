<?php
	try {
		$dbh = new PDO('mysql:host=172.17.0.1;dbname=final', 'admin', '1q2w3e4r', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")); 
		// set the PDO error mode to exception
		$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		echo "Connected successfully"; 
	}
	catch(PDOException $e)
	{
		echo "Connection failed: " . $e->getMessage();
	}

	session_start();

	if(isset($_POST['name'], $_POST['password'])){
		
		$sql = "SELECT * FROM `users` WHERE `name` = :name AND `password` = :password";

		$stmt = $dbh->prepare($sql);
		if($stmt->execute(['name' => $_POST['name'], 'password' => $_POST['password']])){
			$_SESSION['auth'] = $stmt->fetch(PDO::FETCH_ASSOC);
			header('Location: /');
		} else {
			header('Location: /auth.php');
		}

		exit;
	}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel = "stylesheet" type ="text/css" href ="/css/bootstrap.css ">
		<script src = "/js/jquery-1.11.3.min.js"></script>
		<script src = "/js/bootstrap.js"></script>
		<script src = "/js/bootbox.min.js"></script>
		<script src = "/js/script.js"></script>
	</head>
	<body>
		<div class="container">
			<form action="/auth.php" method="post" > 
				<div class="form-group">
					<label for="name">Name</label>
					<input type="text" class="form-control" id="name" name="name" placeholder="Name" value="" />
				</div>
				<div class="form-group">
					<label for="password">password</label>
					<input type="password" class="form-control" id="password" name="password" placeholder="Password" value="" />
				</div>
				<button type="submit" class ="btn btn-primary">Submit</button>
			</form>
		</div>
	</body>
</html>
