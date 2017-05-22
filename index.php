<?php
include 'vendor/autoload.php';
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

if (isset($_POST['request'])) {
	$stmt = $dbh->prepare("
		REPLACE INTO 
			`requests` (
				`id`
				,`request`
				,`status`
				,`userId`
			) 
			VALUES (
				:id
				,:request
				,:status
				,:userId
			)
	");

	$stmt->bindParam(':id', $_POST['id']);
	$stmt->bindParam(':request', $_POST['request']);
	$stmt->bindParam(':status', $_POST['status']);
	$stmt->bindParam(':userId', $_SESSION['auth']['id']);

	$stmt->execute();
	header('Location: /');

	$oQueue = new \App\Queue('init');
	$oQueue->addTask(['id' => $dbh->lastInsertId()]);

	exit;

}else{
	echo '<table class= "table table-striped">
		<thead>
			<tr class = "tr success">
				<th>Request</th>
				<th>Status</th>
			</tr>
		</thead>
	';

	$sql = "SELECT * FROM `requests` WHERE `userId` = :userId";
	$stmt = $dbh->prepare($sql);
	$stmt->execute(['userId' => $_SESSION['auth']['id']]);
	$a= $stmt->fetchAll(PDO::FETCH_ASSOC);

	for($i = 0; $i < count($a); ++$i){
		echo '
			<tr>
				<td class="col-sm-3">'.$a[$i]['request'].'</td>
				<td class="col-sm-3">'.$a[$i]['status'].'</td>
			</tr>
		';
	}
	
	echo '</table>';
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
		<form action="/" method="post" > 
			<div class="form-group">
				<label for="request">Request</label>
				<input type="text" class="form-control" id="request" name="request" placeholder="Request" value="" />
			</div>
			<div class="form-group">
				<label for="status">Status</label>
				<input type="text" class="form-control" id="status" name="status" placeholder="Status" value="" />
			</div>
			<button type="submit" class ="btn btn-primary">Submit</button>
		</form>
		
		</div>
	</body>
</html>

