<?php 

ini_set('display_errors','On');
error_reporting(E_ALL);

define('servername', 'localhost');
define('username', 'root');
define('password', 'rJCa!#7@mgq82hNS');

	
	/***
	 * Grab Member Info by session Id,
	 * This will query Ip.board's member table and session table for an active session.
	 *  @return rows 
	 */
	function getMemberInfoBySessionId($sessionId){
	
		$dbname  = "forums";
		$conn    = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		$stmt = $conn->prepare("SELECT m.member_id, m.donator_points_overall, m.donator_points_current FROM `sessions` s, `members` m where s.id = :sessionId and s.member_id = m.member_id");
		$stmt->execute(array(':sessionId'=> $sessionId));

		$rows = $stmt->fetchAll();

										
		return $rows;
	}
	
    if (isset($_GET['usr'])) {
		$message = "session: " + $_GET['usr']; 
 		error_log($message, 0);
		 
		 echo  $_GET['usr'];
    }
 
	
?>