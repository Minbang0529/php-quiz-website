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
			
			//calcualte max qno for this quiz
			$sql = "SELECT MAX(questionno) as maxqno FROM question WHERE quizID=".$_GET['quizID'];	
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			$maxqno = $stmt->fetch(PDO::FETCH_ASSOC);
			$maxq = $maxqno['maxqno'];
			
			if(empty($_POST))
			{
				newQuizForm($maxq, $pdo);
			}
			else
			{
				getUserInput($maxq, $pdo);
				header('Location: myattempts.php');
			}
			

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

	function newQuizForm($maxq, $pdo)
	{
		//get quiz info
		$qid = $_GET['quizID'];
		$sql = 'SELECT * FROM quiz INNER JOIN user ON quiz.userID = user.userID WHERE quizID = :qid';	
		$stmt = $pdo->prepare($sql);
		$stmt->execute([ 'qid' => $qid ]);
		$quiz = $stmt->fetch(PDO::FETCH_ASSOC);
		
		getDetails($qid, $quiz, $maxq, $pdo);
		echo('<br>');
	}
	
	function getDetails($qid, $quiz, $maxq, $pdo)
	{
		//quiz details
		echo('
				<form method="POST">
					
					<!-- for table quiz -->
					<label class="form-label" for="quizName">Title: </label><br>
					<input class="form-control" type="text" id="quizName" name="quizName" value="'.$quiz['quizName'].'" readonly><br>

					<label class="form-label" for="quizAuthor">Author: </label><br>
					<input class="form-control" type="text" id="quizAuthor" name="quizAuthor" value="'.$quiz['name'].'" readonly><br>

					
					<label class="form-label" for="duration">Duration (minutes): </label><br>
					<input class="form-control" type="text" id="duration" name="duration" value="'.$quiz['duration'].'" readonly><br>
					
					
					');
					
		
		//load all questions
		$qcount = 1;
		
		while( $qcount <= $maxq )
		{
			//initialize options and fill it with empty values
			$option = array();
			$options[1] = '';
			$options[2] = '';
			$options[3] = '';
			$options[4] = '';
			$answer = array();
			$answer[1] = 'value=1 checked';
			$answer[2] = 'value=2';
			$answer[3] = 'value=3';
			$answer[4] = 'value=4';
			$question = array();
			$question['question'] = '';
			$qcontent = '';
			$solution = array();
		
			//select options
			$sql = 'SELECT * FROM quiz INNER JOIN question ON quiz.quizID = question.quizID 
							WHERE question.quizID = :qid AND questionno = :qno';	
			$stmt = $pdo->prepare($sql);
			$stmt->execute([ 'qid' => $qid, 'qno' => $qcount ]);
			$i = 1;
			while($question = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				$qcontent = $question['question'];
				$options[$i] = $question['option'];
				if($question['isAnswer'] == 1)
				{
					$solution[$i] = 1;
				}
				$i++;
			}
			$solarray[$qcount] = $solution;
			
			//omit options if empty in db
			if($options[1] == '')
			{
				$radio1 = '';
			}
			else
			{
				$radio1 = '<input type="radio" class="form-check-input" name="ans'.$qcount.'" '.$answer[1].'>
					<input class="form-control" type="text" id="answer1" name="answer1" value="'.$options[1].'" readonly><br>';
			}
			if($options[2] == '')
			{
				$radio2 = '';
			}
			else
			{
				$radio2 = '<input type="radio" class="form-check-input" name="ans'.$qcount.'" '.$answer[2].'>
					<input class="form-control" type="text" id="answer2" name="answer2" value="'.$options[2].'" readonly><br>';
			}
			if($options[3] == '')
			{
				$radio3 = '';
			}
			else
			{
				$radio3 = '<input type="radio" class="form-check-input" name="ans'.$qcount.'" '.$answer[3].'>
					<input class="form-control" type="text" id="answer3" name="answer3" value="'.$options[3].'" readonly><br>';
			}
			if($options[4] == '')
			{
				$radio4 = '';
			}
			else
			{
				$radio4 = '<input type="radio" class="form-check-input" name="ans'.$qcount.'" '.$answer[4].'>
					<input class="form-control" type="text" id="answer4" name="answer4" value="'.$options[4].'" readonly><br>';
			}
			

			if($qcount == $maxq)
			{
				$nextbutton = '';
			}
			else
			{
				$nextbutton = '<input class="form-control" type="submit" 
												action="quizview.php?quizID='.$_GET['quizID'].' value="Next"><br>';
			}
			
			
			echo('
					<!-- for table question -->
					<label class="form-label" for="question">Question '.$qcount.':</label><br>
					<input class="form-control" type="text" id="question" name="question" value="'
					.$qcontent.'" readonly><br>
					<label class="form-label" for="options">Options: </label><br>
					<div class="form-check">
					'.$radio1.$radio2.$radio3.$radio4.'
					</div><br>'
					
					
			);
			$qcount++;
		}
		echo('<input class="form-control" type="submit" value="Submit"><br>
					
				</form>');
		
	}
	
	function getUserInput($maxq, $pdo)
	{
		$qcount = 1;
		while($qcount <= $maxq)
		{
			//initialize options and fill it with empty values
			$question = array();
			$solution = array();
		
			//select options
			$sql = 'SELECT * FROM quiz INNER JOIN question ON quiz.quizID = question.quizID 
							WHERE question.quizID = :qid AND questionno = :qno';	
			$stmt = $pdo->prepare($sql);
			$stmt->execute([ 'qid' => $_GET['quizID'], 'qno' => $qcount ]);
			$i = 1;
			while($question = $stmt->fetch(PDO::FETCH_ASSOC))
			{
				if($question['isAnswer'] == 1)
				{
					$solution[$i] = 1;
				}
				$i++;
			}
			$solarray[$qcount] = $solution;
			$qcount++;
		}
		//check radio values
		$qcount = 1;
		$score = 0;
		while($qcount <= $maxq)
		{
			if(!empty($solarray[$qcount][$_POST['ans'.$qcount]]))
			{
				$score++;
			}
			$qcount++;
		}
		
		//save attempt
		//save quizID, userID, date, score, attemptno
		
		//get attemptno
		$sql = "SELECT MAX(attemptno) as maxattempt FROM attempt WHERE userID = :uid AND quizID=".$_GET['quizID'];	
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
										'uid' => $_SESSION['userID']
									]);
		$maxattempt = $stmt->fetch(PDO::FETCH_ASSOC);
		if(!empty($maxattempt['maxattempt']))
		{
			$attempt = $maxattempt['maxattempt'] + 1;
		}
		//if no other attempt exists attempt=1
		else
		{
			$attempt = 1;
		}
		$score = $score * 100 / $maxq;
		$date = date('Y-m-d');
		
		$sql = "INSERT INTO `attempt` (quizID, userID, date, score, attemptno)
		        values (:qid, :uid, :date, :score, :attempt)"; 
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
										'qid' => $_GET['quizID'],
										'uid' => $_SESSION['userID'],
										'date' => $date,
										'score' => $score,
										'attempt' => $attempt
									]);
		
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