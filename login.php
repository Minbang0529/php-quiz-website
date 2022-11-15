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
		<h2>Login</h2>
		
		<?php
		//include pdo.php
		include './pdo.php';
		
		//start session
		session_start();
		
		if (empty($_POST))
		{
			echo(showLoginForm());
			echo(buttonToRegister());
		}
		else
		{
			//showPOSTdata();
			
			$user = array();
			$user = checkUserCredentials($pdo);
			if(isset($user['badinput']))
			{
				echo(showLoginForm());
				echo ("Something went wrong: " .$user['badinput']."<br>");
				echo(buttonToRegister());
			}
			else
			{
				$redirect = './quizmain.php';
				header("Location: ".$redirect);
			}
		}
		?>
	</div>

</body>

<?php
function showLoginForm()
{
	return '
		<form method="POST">
		
			<label class="form-label" for="username">Username</label><br>
			<input class="form-control" type="text" id="username" name="username"><br>

			<label class="form-label" for="password">Password</label><br>
			<input class="form-control" type="password" id="password" name="password"><br>
			
			<input class="form-control" type="submit" value="Login"><br>
			
		</form> 
		
	';
}
														

function buttonToRegister()
{
	return '<a class="btn btn-primary mr-2" href="./register.php" role="button">Register new account</a>';
}

function showPOSTdata()
{
	foreach($_POST as $key => $value)
	{
		echo("<br>Inside $key is value: $value");
	}
}

function checkUserCredentials($pdo)
{
	//check if username is empty
	$user = array();
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
	
	//check if username/pw is correct
	$un = $_POST['username'];
	$pw = $_POST['password'];
	
	$sql = 'SELECT * FROM user WHERE username=:un';	
	$stmt = $pdo->prepare($sql);
	$stmt->execute([ 'un' => $un ]);
								
	$userID = null;
	
	while($loginDetail = $stmt->fetch(PDO::FETCH_ASSOC)) 
	{
		//verify password with hashed stored value
		if(password_verify($pw, $loginDetail['password']))
		{
			$userID = $loginDetail['userID'];
			echo($userID);
		}
	}
	
	//if a valid user ID has been found for the username/pw
	
	if(!empty($userID))
	{
		$_SESSION['userID'] = $userID;
	}
	else
	{
		$user['badinput'] = "Username and password doesn't match";
		return $user;
	}
}


?>
</html>