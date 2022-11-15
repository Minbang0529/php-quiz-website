<?php
	//include $pdo from pdo.php
	include './pdo.php';
	
	$qid = $_GET['quizID'];
	$qno = $_GET['questionno'];
	
	//delete specified question
	$sql = "DELETE FROM `question` WHERE quizID=:qid AND questionno=:qno";	
	$stmt = $pdo->prepare($sql);
	$stmt->execute([
									'qid' => $_GET['quizID'],
									'qno' => $qno
								]);
								
								
	//find biggest questionno
	$sql = "SELECT MAX(questionno) as maxq FROM question WHERE quizID = :qid";	
	$stmt = $pdo->prepare($sql);
	$stmt->execute([ 'qid' => $_GET['quizID'] ]);
	$maxqno = $stmt->fetch(PDO::FETCH_ASSOC);
	if(!empty($maxqno['maxq']))
	{
		$maxq = $maxqno['maxq'];
	}
	else
	{
		$maxq = 0;
	}
	echo($maxq);
	//update all questions beyond the deleted one
	$sql = "UPDATE `question` SET questionno=:minusone WHERE quizID=:qid AND questionno=:qno";
	$count = $qno;
	while($count < $maxq)
	{
		$count++;
		$minusone = $count - 1;
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
										'qid' => $_GET['quizID'],
										'qno' => $count,
										'minusone' => $minusone
									
									]);
		echo($count);
	}
	//return to question
	header('Location: editquiz.php?quizID='.$qid.'&questionno='.$qno);
?>