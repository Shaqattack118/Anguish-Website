<?php

/** Shitty API object **/

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting( - 1);



/** POSt routes **/
if(isset($_POST) && !empty($_POST))
{

	$action = $_POST['action'];

	switch($action)
	{
		case 'buyItems': getItems(stripslashes($_GET['category'])); break;
	}
}

/** Get routes **/
if(isset($_GET) && !empty($_GET))
{

	$action = $_GET['action'];

	switch($action)
	{
		case 'getItems': getItems(stripslashes($_GET['category'])); break;
		case 'purchase':
			$items = json_decode($_POST['items']);
			$points = $_POST['points'];
			$usrname = $_POST['name'];
			
			//UPDATE DBS
			
	}

}




function buyItems($json)
{
	//TODO: L
	$cart = json_decode($json);
	
	//query UPDATE `members` SET `donator_points_current`=[value-77] WHERE `member_id` = ?
	

}

/**
* Get donation items
*/
function getItems($json)
{

	$servername = "localhost";
	$username   = "root";
	$password   = "rJCa!#7@mgq82hNS";
	$dbname     = "testDB";

	$conn       = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


	$select     = "SELECT productId, itemId, picture, name, cost, amount FROM donation_items WHERE category = :category";

	$stmt       = $conn->prepare($select);
	$stmt->execute(array(':category'=> $json));

	$rows = $stmt->fetchAll();

	$conn = null;

	die(json_encode($rows));
}


?>
