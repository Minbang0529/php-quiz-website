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
			width: 60em;
		}
		body
		{
			position: relative;
			margin: 1%;
		}
	</style>
	<script language="JavaScript" type="text/javascript">
	function confirmDelete()
	{
		return confirm('Are you sure you want to delete this quiz?');
	}
	</script>
</head>
<body>
	<div id="wrapper">
		
		<?php
		//include pdo.php
		include './pdo.php';
		//start session
		session_start();
		
		echo('<h2>COMP23111 Quiz</h2>');
		
		
		$logout = '<a class="btn btn-primary ml-3" href="logout.php" role="button">Logout</a>';
		$login = '<a class="btn btn-primary ml-3" href="login.php" role="button">Login</a>';
		//get user info if user is logged in, display welcom message + logout button
		if(!empty($_SESSION['userID']))
		{
			$user = getUserInfo($pdo);
			echo('<h2>Welcome, '.$user['username'].'!'.$logout.'</h2>
						<br>');
			echo('<h3>My Quiz Attempts:</h3>');
			echo('*Non-hyperlink quizzes are unavailable');
			getAttempts($user, $pdo);
			echo('<br><a class="btn btn-primary" href="quizmain.php" role="button">Back to All Quizzes</a>');
		}
		//if not logged in, display login button
		else
		{
			echo('<h2>You are not logged in.'.$login.'</h2>');
		}
		
		
		?>
	</div>

</body>

<?php
	function getAttempts($user, $pdo)
	{
		$sql = 'SELECT * FROM attempt INNER JOIN quiz ON attempt.quizID = quiz.quizID WHERE attempt.userID = :uid';	
		$stmt = $pdo->prepare($sql);
		$stmt->execute([ 'uid' => $user['userID'] ]);
		echo('<ul class="list-group">');
		while($attempt = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			//check quiz availability
			if($attempt['availability'] == 1)
			{
				$quizlink = '<a href="quizview.php?quizID='.$attempt['quizID'].'">'
										.$attempt['quizName'].'</a>';
			}
			else
			{
				$quizlink = $attempt['quizName'];
			}
			$date = ': Date Attempted: '.$attempt['date'];
			$attemptno = ', Attempt: '.$attempt['attemptno'];
			$score = ', Score: '.$attempt['score'].'%';
			echo('<li class="list-group-item">'.$quizlink.$date.$attemptno.$score.'</li>');
			
			
		}
		echo('</ul>');
	}
	
	function getUserInfo($pdo)
	{
		$sql = 'SELECT * FROM user WHERE userID=:uid';	
		$stmt = $pdo->prepare($sql);
		$stmt->execute([ 'uid' => $_SESSION['userID'] ]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);
		return $user;
	}
?>
</html>