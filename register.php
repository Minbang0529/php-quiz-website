<!DOCTYPE html>
<html>
<head>
	<!-- using bootstrap css -->
	<meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" 
	integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<style type="text/css">
		#wrapper
		{
			margin: auto;
			width: 25em;
		}
		body
		{
			position: relative;
			margin: 1%;
		}
	</style>
</head>
<body>
	<div id="wrapper">
		<h2>Registration</h2>

		<?php
		//include $pdo from pdo.php
		include './pdo.php';
		
		//log out if already logged in
		session_start();
		if(isset($_SESSION['userID']))
		{
			unset($_SESSION['userID']);
		}
		
		if (empty($_POST))
		{
			echo(showRegisterForm());
			echo(backtoLogin());
		}
		else
		{
			//showPOSTdata();
			
			$user = array();
			$user = getUserCredentials($pdo);
			if(isset($user['badinput']))
			{
				echo(showRegisterForm());
				echo ("Something went wrong: " .$user['badinput']."<br>");
				echo(backtoLogin());
				//echo(backtoRegister());
			}
			else
			{
				registerUser($user, $pdo);
				echo ("<br>Registration complete! Please return to the login page.");
				echo(backtoLogin());
			}
		}
		?>
	</div>

</body>

<?php
function showRegisterForm()
{
	return '
		<form method="POST">
		
			<label class="form-label" for="accType">Account Type</label><br>

			<select class="form-control" name="accType" id="accType">
						<option value = "student">Student</option>
						<option value = "staff">Staff</option>
			</select><br>

			<label class="form-label" for="username">Username</label><br>
			<input class="form-control" type="text" id="username" name="username"><br>

			<label class="form-label" for="name">Name</label><br>
			<input class="form-control" type="text" id="name" name="name"><br>

			<label class="form-label" for="password">Password</label><br>
			<input class="form-control" type="password" id="password" name="password"><br>

			<label class="form-label" for="confirmpw">Confirm Password</label><br>
			<input class="form-control" type="password" id="confirmpw" name="confirmpw"><br>

			
			
			<input class="form-control" type="submit" value="Register"><br>
			
		</form> 
		
	';
}

function registerUser($user, $pdo)
{
	$sql = "SELECT MAX(userID) as maxID FROM user";	
	$stmt = $pdo->prepare($sql);
	$stmt->execute();
	$userID = $stmt->fetch(PDO::FETCH_ASSOC);
	$user['maxID'] = $userID['maxID'] + 1;
								
	$uid = $user['maxID'];							
	$un = $user['username'];
	$pw = $user['password'];
	$pw = password_hash($pw, PASSWORD_DEFAULT);
	$name = $user['name'];
	$accType = $user['accType'];
	
	$sql = "INSERT INTO `user` (userID, username, password, name, accType) 
					VALUES (:userID, :username, :password, :name, :accType)";	
	$stmt = $pdo->prepare($sql);
	$stmt->execute([
									'userID' => $uid,
									'username' => $un,
									'password' => $pw,
									'name' => $name,
									'accType' => $accType
								]);
}

function backtoLogin()
{
	return '<a class="btn btn-primary mr-2" href="./login.php" role="button">Back to Login</a>';
}

function backtoRegister()
{
	return '<a class="btn btn-primary mr-2" href="./register.php" role="button">Back to Register</a>';
}

function showPOSTdata()
{
	foreach($_POST as $key => $value)
	{
		echo("<br>Inside $key is value: $value");
	}
}

function getUserCredentials($pdo)
{
	$user = array();
	//check if username is empty
	if(empty($_POST['username']))
	{
		$user['badinput'] = "Username is required";
		return $user;
	}
	
	//check if username has spaces
	if($_POST['username'] != trim($_POST['username']) || strpos($_POST['username'], ' ') !== false)
	{
		$user['badinput'] = "Username cannot contain spaces";
		return $user;
	}
	
	//check if user exists
	$stmt = $pdo->prepare("SELECT username FROM user WHERE username = :name");
	$stmt->execute(['name' => $_POST['username']]);
	
	if($stmt->rowCount() > 0)
	{
		$user['badinput'] = "Username already exists";
		return $user;
	}
	
	//check if password is empty
	if(empty($_POST['password']))
	{
		$user['badinput'] = "Password is required";
		return $user;
	}
	
	//check if password has spaces
	if($_POST['password'] != trim($_POST['password']) || strpos($_POST['password'], ' ') !== false)
	{
		$user['badinput'] = "password cannot contain spaces";
		return $user;
	}
	
	//check if name is empty
	if(empty($_POST['name']))
	{
		$user['badinput'] = "Name is required";
		return $user;
	}
	//check if name contains special chars
	if(!preg_match('/[A-Za-z]/', $_POST['name']))
	{
		$user['badinput'] = "Name must only contain alphabet letters";
		return $user;
	}
	//check if password confirm is correct
	if($_POST['password'] != $_POST['confirmpw'])
	{
		$user['badinput'] = "Passwords don't match";
		return $user;
	}
	
	$user['username'] = $_POST['username'];
	$user['password'] = $_POST['password'];
	$user['name'] = $_POST['name'];
	$user['accType'] = $_POST['accType'];
	return $user;
}


?>
</html>