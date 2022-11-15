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
	function confirmExit()
	{
		return confirm('Exit without saving?');
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
			
			//if nothing has been submitted
			if(empty($_POST))
			{
				newQuizForm($user, $pdo);
			}
			//if user has submitted
			else
			{
				//get user input
				$qdetail = array();
				$qdetail = getUserInput();
				
				if(isset($qdetail['badinput']))
				{
					newQuizForm($user, $pdo);
					echo('Something went wrong: '.$qdetail['badinput'].'<br>');
				}
				//if user input is valid, update question to database
				else
				{
					$qinfo = array();
					$qid = saveQuiz($qdetail, $pdo);
					header('Location: editquiz.php?quizID='.$qid);
				}
			}
			//go back to main page
			echo('<br><a class="btn btn-primary" href="quizmain.php" role="button">Back to All Quizzes</a>');
		}
		else
		{
			echo('<h2>You are not logged in.'.$login.'</h2>');
		}
		
		
		?>
	</div>

</body>

<?php
	$globalqno = 0;

	function newQuizForm($user, $pdo)
	{
		if(!empty($_GET['quizID']))
		{
			//edit mode
			$qid = $_GET['quizID'];
			$sql = 'SELECT * FROM quiz INNER JOIN user ON quiz.userID = user.userID WHERE quizID = :qid';	
			$stmt = $pdo->prepare($sql);
			$stmt->execute([ 'qid' => $qid ]);
			$quiz = $stmt->fetch(PDO::FETCH_ASSOC);
		}
		else
		{
			//new quiz
			$qid = -1;
			$quiz = array();
			$quiz['quizID'] = -1;
			$quiz['quizName'] = '';
			
			$uid = $_SESSION['userID'];
			$sql = 'SELECT * FROM user WHERE userID = :uid';	
			$stmt = $pdo->prepare($sql);
			$stmt->execute([ 'uid' => $uid ]);
			$userid = $stmt->fetch(PDO::FETCH_ASSOC);
			$quiz['name'] = $userid['name'];
			$quiz['duration'] = '';
		}
		
		echo(getEditFields($qid, $quiz, $pdo));
		echo('<br>');
	}
	
	function getEditFields($qid, $quiz, $pdo)
	{
		$checked = 'value="value"';

		
		$qid = $quiz['quizID'];
		//if no question is given load q1
		
		return '
			<form method="POST">
				
				<!-- for table quiz -->
				<label class="form-label" for="quizName">Title: </label><br>
				<input class="form-control" type="text" id="quizName" name="quizName" value="'.$quiz['quizName'].'"><br>

				<label class="form-label" for="quizAuthor">Author: </label><br>
				<input class="form-control" type="text" id="quizAuthor" name="quizAuthor" value="'.$quiz['name'].'" readonly><br>
				
				<div class="form-check">
				
				<input type="checkbox" class="form-check-input" id="availability" name="availability" '.$checked.'>
				<label class="form-check-label" for="availability">Quiz Available?</label><br>
				</div><br>
				
				<label class="form-label" for="duration">Duration (minutes): </label><br>
				<input class="form-control" type="text" id="duration" name="duration" value="'.$quiz['duration'].'"><br>
				
				<input class="form-control" type="submit" value="Save"><br>
				
			</form> 
				
		';

	}
	
	function getUserInput()
	{
		$qdetail = array();
		
		// if quiz name is empty
		if(empty($_POST['quizName']) || trim($_POST['quizName']) == '')
		{
			$qdetail['badinput'] = "Quiz name cannot be empty";
			return $qdetail;
		}
		
		// if quiz duration is empty
		if(empty($_POST['duration']) || trim($_POST['duration']) == '')
		{
			$qdetail['badinput'] = "Quiz duration cannot be empty";
			return $qdetail;
		}
		
		// if quiz duration isn't numeric
		if(!is_numeric($_POST['duration']))
		{
			$qdetail['badinput'] = "Quiz duration must be a number";
			return $qdetail;
		}
		
		
		$qdetail['quizName'] = $_POST['quizName'];
		$qdetail['duration'] = $_POST['duration'];
		if(isset($_POST['availability']))
		{
			$qdetail['availability'] = 1;
		}
		else
		{
			$qdetail['availability'] = 0;
		}
		
		return $qdetail;
	}
	
	function saveQuiz($qdetail, $pdo)
	{
		//get quiz id
		$sql = "SELECT MAX(quizID) as maxID FROM quiz";	
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		//if other quiz exists then increment 1 to max qid
		$maxqid = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!empty($maxqid['maxID']))
		{
			$qid = $maxqid['maxID'] + 1;
		}
		//if no other quiz exists qid=1
		else
		{
			$qid = 1;
		}
		$auth = $_SESSION['userID'];

		//create or update quiz information
		$quizname = $qdetail['quizName']; 
		$avail = $qdetail['availability'];
		$dura = $qdetail['duration'];
		
		$sql = "INSERT INTO `quiz` (quizID, quizName, userID, availability, duration)
		        values (:qid, :quizname, :auth, ".$avail.", :dura)";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
										'qid' => $qid,
										'quizname' => $quizname,
										'auth' => $auth,
										'dura' => $dura,
									]);
		return $qid;
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