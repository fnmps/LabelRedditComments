<?php
session_start();
$config['sess_use_database'] = TRUE;
$config['sess_match_ip'] = TRUE;

if(isset($_SESSION['author'])){
	$author = $_SESSION['author'];
}else{

	return;
}


$servername = "#########"; 		// -> your database host, usually localhost
$username = "#########";		// -> your database username
$password = "#########";		// -> your username password
$dbname = "#########";			// -> your database name
$tablecommentname = "#########"; // -> the name of the table where the comments to be labeled are
$tablelabeledname = "#########"; // -> the name of the table where the labeled comments should be

$author = $_SESSION['author'];
ignore_user_abort (TRUE );




$conn = new mysqli ( $servername, $username, $password, $dbname );
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
	die ( "Connection failed: " . $conn->connect_error );
}


if(isset($_POST['action']) && !empty($_POST['action'])) {
	$action = $_POST['action'];
	
	switch($action) {
		case 'getComments' : getComments($conn);break;
		case 'submitAnswer' : submitAnswer($conn);break;
		case 'skipAnswer' : skipAnswer($conn);break;
		default: return;
	}
}


function getComments($conn){
	$author = $_SESSION['author'];
	$code = $_POST["code"];
	
	$sql = "SELECT id, comment, permalink FROM $tablecommentname t1 WHERE t1.author='$author' LIMIT 1";
	$result = $conn->query ( $sql );
	
	$data = array ();
	
	if ($result->num_rows > 0) {
		
		$sql = "SELECT id, comment, permalink FROM $tablecommentname t1 WHERE t1.author='$author' AND t1.id NOT IN (SELECT comment_id FROM $tablelabeledname t2 WHERE t1.id=t2.comment_id)";
		$result = $conn->query ( $sql );
		
		while ( $row = $result->fetch_assoc () ) {
			$data [] = $row;
		}
	} else {
		exec("python getusercomments.py $author $code") or die('error executing python script');
	
		$sql = "SELECT id, comment, permalink FROM $tablecommentname WHERE author='$author'";
		$result = $conn->query ( $sql );
	
		if ($result->num_rows > 0) {
			while ( $row = $result->fetch_assoc () ) {
				$data [] = $row;
			}
		}
	}
	$conn->close ();
	
	print json_encode($data);
}

function submitAnswer($conn){
	$author = $_SESSION['author'];
	$answer = $_POST ["answer"];
	$comment_id = $_POST ["id"];
	$segment_id = $_POST ["segment_id"];
	$segment = $conn->real_escape_string($_POST ["segment"]);
	
	
	
	$sql = "INSERT INTO $tablelabeledname (comment_id, comment_segment_id, author, comment_segment, label) VALUES ($comment_id, $segment_id, '$author', '$segment', '$answer') ON DUPLICATE KEY UPDATE label='$answer'";
	
	if ($conn->query($sql) === TRUE) {
		echo "ok";
	} else {
		//echo "Error: " . $sql . "<br>" . $conn->error;
		echo "An error occurred while submitting.\nPlease try again later.";
	}
	
	$conn->close();
}

?>

