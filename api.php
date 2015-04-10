<?php 

 /** Shitty API object **/
 
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);
	


	 /** POSt routes **/
		if(isset($_POST) && !empty($_POST)) {
    
 	  	$action = $_POST['Action'];
   
		   switch($action) {
		       // case 'getItems': getItems(stripslashes($_POST['JSON'])); break;
				}
		}
		
		/** Get routes **/
		if(isset($_GET) && !empty($_GET)) {
    
 	  	$action = $_GET['action'];
   
	   switch($action) {
	        case 'getItems': getItems(stripslashes($_GET['category'])); break;
			}
		
		}	
		
		
		
		
		/**
		 * Get donation items
		 */
		function getItems($json){

				$servername = "localhost";
				$username = "root";
				$password = "rJCa!#7@mgq82hNS";
				$dbname = "testDB";
	
				$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
			
				$select = "SELECT itemId, picture, name, cost, amount FROM donation_items WHERE category = :category";
				
				$stmt = $conn->prepare($select);
				$stmt->execute(array(':category' => $json));

				$rows = $stmt->fetchAll();

				$conn = null;
				
				die(json_encode($rows));
		}


?>
