<?php

/** Shitty API object **/

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting( - 1);

define('servername', 'localhost');
define('username', 'root');
define('password', 'rJCa!#7@mgq82hNS');

$pinArrays = array();


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
		case 'getPurchaseHistory': getPurchaseHistory(stripslashes($_GET['sessionId']));
		
		case 'getItems': getItems(stripslashes($_GET['category'])); break;
	}

}


function redemationHistory($post){
	
}


/** Get Tokens for member Id **/
function getTokens($memberId){

	$dbname   = "forums";
	$conn      = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $select  = "SELECT donator_points_current FROM members WHERE member_id = :memberId";
 

	$stmt   = $conn->prepare($select);
	$stmt->execute(array(':memberId'=> $memberId));

	$rows = $stmt->fetchAll();

	$conn = null;
	
	return $rows[0];
}

function generateString($length){
	return substr(str_shuffle(md5(time())),0,$length);;
}
/** Generate Donator Pin **/
function generateDonatorPin(){
	$length = 9;
	return strtoupper("A".generateString($length));
}

function generateTransactionId(){
	$length = 10;
	return strtoupper("TRAN_".generateString($length));
}


function purchasePin($productId){
	
}

function removePoints($memberId, $beforePoints, $points){
	
	
	$dbname   = "forums";
	$conn      = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  $select  = "UPDATE members 
	       		 SET donator_points_current=?
	       		 WHERE member_id=?";
  
  $newPoints = $beforePoints - $points;
  
	$stmt   = $conn->prepare($select);
	$stmt->execute(array($newPoints,$memberId));
	
}

function givePlayerItem($json){
	$dbname     = "testDB";
	$conn      = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
	$select  = "INSERT INTO `donation_claim`(`item_id`, `amount`, `claimed`, `username`)  VALUES (:itemId,:amount,0 ,:username)";
	$stmt   = $conn->prepare($select);

	$stmt->execute($json);

 	$conn = null;
 	
}

function purchaseItemHistoryInsert($json){
	$dbname     = "testDB";
	$conn      = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
	$select  = "INSERT INTO `donation_transactions`(`memberId`, `username`, `productId`, `amount`, `price`, `transactionId`, `boughtdate`) VALUES (:memberId,:username,:productId,:amount,:price,:transactionId, sysdate(3))";
	$stmt   = $conn->prepare($select);

	$stmt->execute($json);

 	$conn = null;
 	
}

function getPurchaseHistory($sessionId)
{
	
	$memberId = getMemberIdBySessionId($sessionId);

	$dbname     = "testDB";
	$conn      = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
	$select  = "	SELECT username,	productId, 	amount, 	price, 	boughtdate, 	transactionId FROM `donation_transactions` where memberId = :memberId order by boughtdate";

	$stmt     = $conn->prepare($select);
	$stmt->execute(array(':memberId'=> $memberId));

	$rows = $stmt->fetchAll();

 	$conn = null;
 	
	die(json_encode($rows));

}
/**
 * Select memberId by SessionId
 **/
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
				die(returnMessage('Invalid Session Id', 460));
				
				
				
	return $rows[0]['member_id'];
}


/* purchase Action **/
function purchase($post)
{
	$cart = $post['cart'];
	$username = $post['username'];
	$sessionId = $post['sessionId'];
	
	$memberId = getMemberIdBySessionId($sessionId);
	
	/** no username **/
	if (empty($username))
			die(returnMessage('Please enter a username!', 450));

	$currentTokens = getTokens($memberId);
	$productArr = getItemsById($cart);
	
	$currentCost = 0;
	/** Select products and make sure this user has enough tokens **/
	foreach ($productArr as $product) {
    	$currentCost += $product['cost'];
	}
	
	/** They do not have the correct amount of points **/
	if($currentCost > $currentTokens['donator_points_current'])
		die(returnMessage('You do not have enough tokens for this purchase!', 440));
	
	
	
	$transId = generateTransactionId();
	$beforePoints = $currentTokens['donator_points_current'];
	/** loop through our products **/
	foreach ($productArr as $product) {
  
    $cost = $product['cost'];
		$productId = $product['productId'];
		$itemId = $product['itemId'];
		$amount = $product['amount'];
		 
		$history = array(
					'memberId' => $memberId,
					'username' => $username,
					'productId' => $productId,
					'amount' => $amount,
					'price' => $cost,
					'transactionId' => $transId
					);

		$playerItemObj = array(
					'itemId' => $itemId,
					'username' => $username,
					'amount' => $amount
					);
		
		/** remove Points **/
		removePoints($memberId,  $beforePoints, $cost);
		purchaseItemHistoryInsert($history);
		
		/** give player the item **/
		givePlayerItem($playerItemObj);
		
		$beforePoints = $beforePoints - $cost;
	}

		die(returnMessage('Success', 200));
}

/**
* Get donation items
*/
function getItems($json)
{
	$dbname     = "testDB";
	$conn      = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
	$select  = "SELECT productId, itemId, picture, name, cost, amount FROM donation_items WHERE category = :category";

	$stmt     = $conn->prepare($select);
	$stmt->execute(array(':category'=> $json));

	$rows = $stmt->fetchAll();

 $conn = null;
	die(json_encode($rows));
}

function getItemsById($productIds)
{
	$dbname     = "testDB";
	$conn      = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);

	
	$select  = "SELECT productId, itemId, picture, name, cost, amount FROM donation_items WHERE productId IN (".str_pad('',count($productIds)*2-1,'?,').");";

	$stmt     = $conn->prepare($select);
	$stmt->execute($productIds);

	$rows = $stmt->fetchAll();

 $conn = null;

	return $rows;
}


function returnMessage($message, $code){
	return json_encode(array(
					'Message' => $message,
					'Code' => $code
					));
	
}

?>
