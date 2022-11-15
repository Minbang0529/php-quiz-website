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
		
		//get user information
		
		$logout = '<a class="btn btn-primary ml-3" href="logout.php" role="button">Logout</a>';
		$login = '<a class="btn btn-primary ml-3" href="login.php" role="button">Login</a>';
		//if user is logged in
		if(!empty($_SESSION['userID']))
		{
			$user = getUserInfo($pdo);
			echo('<h2>Welcome, '.$user['username'].'!'.$logout.'</h2>
						<br>');
			echo('<h3>All Quizzes:</h3>');
			echo('*Non-hyperlink quizzes are unavailable');
			getAllQuiz($user, $pdo);
			echo('<br><a class="btn btn-primary" href="myattempts.php" role="button">My Attempts</a>');
			//check account type again, for new quiz button
			if($user['accType'] == 'student')
			{
				$newquiz = '';
			}
			else
			{
				$newquiz = '<a class="btn btn-primary ml-3" href="newquiz.php" role="button">New Quiz</a>';
			}
			echo($newquiz);
		}
		else
		{
			echo('<h2>You are not logged in.'.$login.'</h2>');
		}
		
		
		?>
	</div>

</body>

<?php
	function getAllQuiz($user, $pdo)
	{
		$sql = 'SELECT * FROM quiz';	
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		echo('<ul class="list-group">');
		while($quiz = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			if($quiz['availability'] == 1)
			{
				$quizlink = '<a href="quizview.php?quizID='.$quiz['quizID'].'">'.$quiz['quizName'].'</a>';
			}
			else
			{
				$quizlink = $quiz['quizName'];
			}
			//check account type
			if($user['accType'] == 'student')
			{
				$editquiz = '';
				$delquiz = '';
			}
			else
			//set color scheme for availability
			{
				if($quiz['availability'] == 1)
				{
					$editquiz = '<a class="btn btn-primary ml-3" href="editquiz.php?quizID='.$quiz['quizID'].
											'" role="button">Edit</a>';
					$delquiz = '<a class="btn btn-primary ml-2" href="deletequiz.php?quizID='.$quiz['quizID'].
										 '" onclick="return confirmDelete()" role="button">Delete</a>';
				}
				else
				{
					$editquiz = '<a class="btn btn-secondary ml-3" href="editquiz.php?quizID='.$quiz['quizID'].
											'" role="button">Edit</a>';
					$delquiz = '<a class="btn btn-secondary ml-2" href="deletequiz.php?quizID='.$quiz['quizID'].
										 '" onclick="return confirmDelete()" role="button">Delete</a>';
				}
			}
			echo('<li class="list-group-item">'.$quizlink.$editquiz.$delquiz.'</li>');
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