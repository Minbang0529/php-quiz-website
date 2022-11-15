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
			//if user has saved question
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
					saveQuestion($qdetail, $pdo);
					newQuizForm($user, $pdo);
					echo("Question Saved!<br>");
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
		echo(getButtons($pdo));
		echo('<br>');
	}
	
	function getEditFields($qid, $quiz, $pdo)
	{
		if ($quiz['availability'] == 1)
		{
			$checked = 'value="value" checked';
		}
		else
		{
			$checked = 'value="value"';
		}
		
		//initialize options and fill it with empty values
		$option = array();
		$options[1] = '';
		$options[2] = '';
		$options[3] = '';
		$options[4] = '';
		$answer = array();
		$answer[1] = 'value="value"';
		$answer[2] = 'value="value"';
		$answer[3] = 'value="value"';
		$answer[4] = 'value="value"';
		$question = array();
		$question['question'] = '';
		$qno = -1;
		$qcontent = '';
		$qid = $quiz['quizID'];
		//if no question is given load q1
		if(empty($_GET['questionno']))
		{
			$_GET['questionno'] = 1;
		}
			
		//load question
		$qno = $_GET['questionno'];
		$sql = 'SELECT * FROM quiz INNER JOIN question ON quiz.quizID = question.quizID 
						WHERE question.quizID = :qid AND questionno = :qno';	
		$stmt = $pdo->prepare($sql);
		$stmt->execute([ 'qid' => $qid, 'qno' => $qno ]);
		$i = 1;
		while($question = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			$qcontent = $question['question'];
			$options[$i] = $question['option'];
			if($question['isAnswer'] == 1)
			{
				$answer[$i] = 'value="value" checked';
			}
			$i++;
		}
		global $globalqno;
		$globalqno = $qno;
		
		return '
			<form action = "editquiz.php?quizID='.$qid.'&questionno='.$globalqno.'" method="POST">
				
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
				
				<label class="form-label" for="question">Question '.$qno.':</label><br>
				<input class="form-control" type="text" id="question" name="question" value="'
				.$qcontent.'"><br>
				
				<!-- for table question -->
				<label class="form-label" for="options">Options (Check to indicate answer): </label><br>
				<div class="form-check">
				<input type="checkbox" class="form-check-input" name="check1" '.$answer[1].'>
				<input class="form-control" type="text" id="answer1" name="answer1" value="'.$options[1].'"><br>
				
				<input type="checkbox" class="form-check-input" name="check2" '.$answer[2].'>
				<input class="form-control" type="text" id="answer2" name="answer2" value="'.$options[2].'"><br>
				
				<input type="checkbox" class="form-check-input" name="check3" '.$answer[3].'>
				<input class="form-control" type="text" id="answer3" name="answer3" value="'.$options[3].'"><br>
				
				<input type="checkbox" class="form-check-input" name="check4" '.$answer[4].'>
				<input class="form-control" type="text" id="answer4" name="answer4" value="'.$options[4].'"><br>
				</div><br>
				
				
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
		
		// if question is empty
		if(empty($_POST['question']) || trim($_POST['question']) == '')
		{
			$qdetail['badinput'] = "Question cannot be empty";
			return $qdetail;
		}
		
		// if all options are empty
		if((empty($_POST['answer1']) || trim($_POST['answer1']) == '') &&
			 (empty($_POST['answer2']) || trim($_POST['answer2']) == '') &&
			 (empty($_POST['answer3']) || trim($_POST['answer3']) == '') &&
			 (empty($_POST['answer4']) || trim($_POST['answer4']) == ''))
		{
		  $qdetail['badinput'] = "You need at least one non-empty option";
			return $qdetail;
		}
		
		// if none of the options are checked
		if(!isset($_POST['check1']) && !isset($_POST['check2']) &&
			 !isset($_POST['check3']) && !isset($_POST['check4']))
		{
		  $qdetail['badinput'] = "You need at least one answer";
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
		
		
		$option = array();
		$answer = array();
		global $globalqno;
		$qdetail['questionno'] = $globalqno;
		$qdetail['question'] = $_POST['question'];
		//set options and answers using 2d array
		$option[1] = $_POST['answer1'];
		$option[2] = $_POST['answer2'];
		$option[3] = $_POST['answer3'];
		$option[4] = $_POST['answer4'];
		$qdetail['option'] = $option;
		//convert checkboxes to correct input values
		if(isset($_POST['check1']))
		{
			$answer[1] = 1;
		}
		else
		{
			$answer[1] = 0;
		}
		if(isset($_POST['check2']))
		{
			$answer[2] = 1;
		}
		else
		{
			$answer[2] = 0;
		}
		if(isset($_POST['check3']))
		{
			$answer[3] = 1;
		}
		else
		{
			$answer[3] = 0;
		}
		if(isset($_POST['check4']))
		{
			$answer[4] = 1;
		}
		else
		{
			$answer[4] = 0;
		}
		$qdetail['answer'] = $answer;
		return $qdetail;
	}
	
	function saveQuestion($qdetail, $pdo)
	{
		//if quizID is given
		if(!empty($_GET['quizID']))
		{
			$qid = $_GET['quizID'];
			$sql = "SELECT userID FROM quiz WHERE quizID = :qid";	
			$stmt = $pdo->prepare($sql);
			$stmt->execute([ 'qid' => $qid ]);
			$userid = $stmt->fetch(PDO::FETCH_ASSOC);
			$auth = $userid['userID'];
		}
		//if new quiz
		else
		{
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
		}
		
		//create or update quiz information
		$quizname = $qdetail['quizName']; 
		$avail = $qdetail['availability'];
		$dura = $qdetail['duration'];
		
		$sql = "INSERT INTO `quiz` (quizID, quizName, userID, availability, duration)
		        values (:qid, :quizname, :auth, ".$avail.", :dura) 
						ON DUPLICATE KEY UPDATE quizName = :quiznameu, availability = ".$avail.",
						                       duration = :durau;";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
										'qid' => $qid,
										'quizname' => $quizname,
										'quiznameu' => $quizname,
										'auth' => $auth,
										'dura' => $dura,
										'durau' => $dura
									]);
		//fetch values and insert/update question and options
		$question = $qdetail['question'];
		$qno = $_GET['questionno'];
		$i = 1;
		
		
		
		//do so for all 4 options
		while($i < 5)
		{
			global $globalqno;
			$opt = $qdetail['option'][$i];
			$optnum = $i;
			$isans = $qdetail['answer'][$i];
			$sql = "REPLACE INTO `question` (quizID, question, questionno, option, optnumber, isAnswer) 
					  VALUES (:qid, :question, :qno, :opt, :optnum, ".$isans.")";	
			
			$stmt = $pdo->prepare($sql);
			$stmt->execute([
											'qid' => $qid,
											'question' => $question,
											'qno' => $qno,
											'opt' => $opt,
											'optnum' => $optnum
										]);
			$i++;
		}
	}
	
	function getButtons($pdo)
	{
		$qid = $_GET['quizID'];
		$qno = $_GET['questionno'];
		$exists = 0;
		//delete button
		//check if question exists in db
		$sql = "SELECT * FROM question WHERE quizID = :qid AND questionno = :qno";	
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
											'qid' => $qid,
											'qno' => $qno
										]);
		if($checkq = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			echo('<a class="btn btn-primary ml-3" href="deletequestion.php?quizID='.$qid.
						'&questionno='.$qno.'" role="button">Delete</a>');
		  $exists = 1;
		}
		
		//previous Q button
		//check if previous question exists in db
		$qno = $qno - 1;
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
											'qid' => $qid,
											'qno' => $qno
										]);
		if($checkq = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			echo('<a class="btn btn-primary ml-3" href="editquiz.php?quizID='.$qid.
						'&questionno='.$qno.'" role="button">Prev</a>');
		}
		
		//next Q button
		//check if next question exists in db
		$qno = $qno + 2;
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
											'qid' => $qid,
											'qno' => $qno
										]);
		if($checkq = $stmt->fetch(PDO::FETCH_ASSOC))
		{
			echo('<a class="btn btn-primary ml-3" href="editquiz.php?quizID='.$qid.
						'&questionno='.$qno.'" role="button">Next</a>');
		}
		//new Q button
		else if($exists == 1)
		{
			echo('<a class="btn btn-primary ml-3" href="editquiz.php?quizID='.$qid.
						'&questionno='.$qno.'" role="button">New</a>');
		}
		
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