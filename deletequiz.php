<?php
	//include $pdo from pdo.php
	include './pdo.php';
	
	//delete questions associated with quiz first
	$sql = "DELETE FROM `question` WHERE quizID=:qid";	
	$stmt = $pdo->prepare($sql);
	$stmt->execute([
									'qid' => $_GET['quizID']
								]);
	
	//delete attempts associated with attempt as well
	$sql = "DELETE FROM `attempt` WHERE quizID=:qid";	
	$stmt = $pdo->prepare($sql);
	$stmt->execute([
									'qid' => $_GET['quizID']
								]);
	
	//delete actual quiz entry
	$sql = "DELETE FROM `quiz` WHERE quizID=:qid";	
	$stmt = $pdo->prepare($sql);
	$stmt->execute([
									'qid' => $_GET['quizID']
								]);
								
	
	
	//return to quiz list
	header('Location: quizmain.php');
?>