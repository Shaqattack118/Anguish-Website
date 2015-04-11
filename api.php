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
		case 'purchase': purchase($_POST); return;
	}
}

/** Get routes **/
if(isset($_GET) && !empty($_GET))
{

	$action = $_GET['action'];

	switch($action)
	{
		case 'getItems': getItems(stripslashes($_GET['category'])); break;
	}

}


function redemationHistory($post){
	
}

function purchaseHistory($post){
	
	
}


function purchase($post)
{
	$cart = $post['cart'];
	$username = $post['username'];
	$memberId = $post['memberId'];
	
	
	/** Select products and make sure this user has enough tokens **/
	
	
	/** remove points from User **/
	
	
	/** give items **/
	
	
	
	/** success message **/
	
	
	
	print_r($cart);
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
