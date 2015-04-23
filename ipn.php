<?php 


ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting( - 1);



define("TO_ROOT", "./");
define("ASSETS", TO_ROOT . "bin/php/");

require_once(ASSETS . 'data.php');

$GLOBALS['bmtProductIds'] = array(
					91390007 => '50',
					91390006 => '10',
					91390005 => '25'
	);
					
function getMemberIdBySessionId($sessionId){

	$dbname   = "forums";
	$conn      = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $select  = "SELECT member_id FROM `sessions` where id = :sessionId";
 

	$stmt   = $conn->prepare($select);
	$stmt->execute(array(':sessionId'=> $sessionId));

	$rows = $stmt->fetchAll();

	$conn = null;
	
		/** Invalid Session Id **/
	if(empty($rows))
				die('Invalid Session Id'));
				
				
				
	return $rows[0]['member_id'];
}

				
	function addPoints($memberId, $points){
		
		
		$dbname   = "forums";
		$conn      = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$select  = "UPDATE members 
				   SET donator_points_current=donator_points_current+?
				   WHERE member_id=?";
	  
		$stmt   = $conn->prepare($select);
		$stmt->execute(array($points,$memberId));
		
	}


	echo '<?xml version="1.0" encoding="utf-8"?>';
	echo '<response>';
	echo '<registrationkey>';
	$bmtparser = new BMTXMLParser ();
	if ($bmtparser->parse (file_get_contents('php://input'))) {   
	   # keycount is normally 1. However, if the product option "Use one
	   # key" in the vendor area has been unchecked, BMT Micro expects
	   # you to send back as many keys as the number of items (quantity)
	   # ordered. The variable keycount represents thef number of keys
	   # that the system expects to receive back from you.
	   $file = fopen("log.txt", "a");
	   fwrite($file, print_r($bmtparser, true));
	   $keycount = $bmtparser->getElement ('keycount');

	   $data = $bmtparser->tag_data;
	   $userId= getMemberIdBySessionId($data['ccom']);
	   $pid = $data['productid'];
	   
	   $value = $GLOBALS['bmtProductIds'][$pid];
	   
	   addPoints($userId, $value);
	   
	   for ($key = 1; $key <= $keycount; $key++) {
		  $keydata = 'The registration key for ' . $bmtparser->getElement ('registername') . ' is ' . $key;
		  echo '<keydata>' . $keydata . '</keydata>';
		}
	} else {
	   echo '<errorcode>1</errorcode>';
	   echo '<errormessage>' . $bmtparser->getElement ('error') . '</errormessage>';
	   }
	echo '</registrationkey>';
	echo '</response>';
?>