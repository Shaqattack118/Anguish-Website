<?php

/** Shitty API object **/

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting( - 1);


define("TO_ROOT", "./");
define("ASSETS", TO_ROOT . "bin/php/");

require_once(ASSETS . 'data.php');
require_once(ASSETS . 'StringBuilder.php');
require_once(TO_ROOT . 'ipbwi/ipbwi.inc.php');

$GLOBALS['pinArray'] = array(
					1 => 'Donator_Pin',
					2 => 'Super_Donator_Pin',
					3 => 'Five_Pack_Donator_Pin',
					4 => 'Drop_Pin'
					);



	 
/** POSt routes **/
if(isset($_POST) && !empty($_POST))
{

	$action = $_POST['action'];

	switch($action)
	{
		case 'purchase': purchase($_POST); return;
		case 'ipn' : ipn($_POST); return;
		case 'redeemPIn' : redeemPin($_POST); return;
	}
}

/** Get routes **/
if(isset($_GET) && !empty($_GET))
{

	$action = $_GET['action'];

	switch($action)
	{
		case 'getRedemptionHistory': getRedemptionHistory(stripslashes($_GET['sessionId']));
		case 'checkPin': checkPin(stripslashes($_GET['pin'])); break;
		case 'getAllItems': getAllItems(); break;
		case 'getItems': getItems(stripslashes($_GET['category'])); break;
	}

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
	return strtoupper("D".generateString($length));
}

function generateSuperDonatorPin(){
	$length = 9;
	return strtoupper("SD".generateString($length));
	
}

function generateDropPin(){
	$length = 9;
	return strtoupper("DR".generateString($length));
	
}

function generateTransactionId(){
	$length = 10;
	return strtoupper("TRAN_".generateString($length));
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


function givePlayerItem($json, 	$conn){
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

function getRedemptionHistory($sessionId)
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
	
	$pinArray = $GLOBALS['pinArray'];
	
	
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
					'memberId' => $memberId,
					'productId' => $productId,
					'username' => $username,
					'amount' => $amount
					);
		
		/** remove Points **/
		removePoints($memberId,  $beforePoints, $cost);
		purchaseItemHistoryInsert($history);
		
		if (array_key_exists($productId, $pinArray))
			givePinToPlayer($playerItemObj);
		else
			givePlayerItem($playerItemObj);
		
		
		$beforePoints = $beforePoints - $cost;
		
	}
	
	die(returnMessage("success", 200));

}

function givePinToPlayer($playerItemObj){
	
	$productId = $playerItemObj['productId'];
	$memberId = $playerItemObj['memberId'];
	$pinArray = $GLOBALS['pinArray'];
	

	switch($pinArray[$productId]){
		case 'Donator_Pin':
				
					$pin = generateDonatorPin();
					
					$pinData = array(
						'pin' => $pin,
						'hasRedeemed' => 0,
						'type' => 0
					);
			
				insertIntoPinTable($pinData);
					
				$pinMessage = "Thank you purchasing a donator pin! Make sure you write down your pin and keep it in a safe place. <br> Pin: ".$pin." \n";
				$title = "Your Donator Pin [DO NOT REPLY]";				
				sendPM($memberId, $title , $pinMessage);
								
		break;
		case 'Super_Donator_Pin':
				
					$pin = generateSuperDonatorPin();
					
					$pinData = array(
						'pin' => $pin,
						'hasRedeemed' => 0,
						'type' => 1
					);
			
				insertIntoPinTable($pinData);
					
				$pinMessage = "Thank you purchasing a super donator pin! Make sure you write down your pin and keep it in a safe place. <br> Pin: ".$pin." \n";
				$title = "Your Super Donator Pin [DO NOT REPLY]";				
				sendPM($memberId, $title , $pinMessage);
								
		break;			
		case 'Drop_Pin':
				
					$pin = generateDropPin();
					
					$pinData = array(
						'pin' => $pin,
						'hasRedeemed' => 0,
						'type' => 2
					);
			
				insertIntoPinTable($pinData);
					
				$pinMessage = "Thank you purchasing a 15% drop pin! Make sure you write down your pin and keep it in a safe place. <br> Pin: ".$pin." \n";
				$title = "Your Drop Pin [DO NOT REPLY]";				
				sendPM($memberId, $title , $pinMessage);
								
		break;			
			
		case 'Five_Pack_Donator_Pin':
					

			$pins = "";
		
			$first = true;
			
			for ($i = 0; $i < 5; $i++) {
			
				$pin = generateDonatorPin();
			
				$pinData = array(
					'pin' => $pin,
					'hasRedeemed' => 0,
					'type' => 0
				);
			
				insertIntoPinTable($pinData);
				
				if($first){
					$pins = $pin;
					$first = false;
				} else
					$pins = $pins . ' , ' . $pin; 
			}				
			
			$pinMessage = "Thank you purchasing the 5 donator pin pack! Make sure you write down your pins and keep them in a safe place. <br> Pins: ".$pins." \n";
			$title = "Your Five Donator Pack [DO NOT REPLY]";				
				
			sendPM($memberId, $title , $pinMessage);
		
		break;

	}
	
}

function insertIntoPinTable($json){
	$dbname     = "testDB";
	$conn      = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
	
	
	$select  = "INSERT INTO `donation_pins`(`pin`, `hasRedeemed`, `generateDate`, `type`) VALUES (:pin,:hasRedeemed,sysdate(3),:type)";
	$stmt   = $conn->prepare($select);


	$stmt->execute($json);

 	$conn = null;
	
}


function getPinData($pin){
	$dbname     = "testDB";
	$conn      = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
	
	$select  = "SELECT pin, generateDate, hasRedeemed FROM donation_pins WHERE pin = :pin ";
	$stmt   = $conn->prepare($select);

	$stmt->execute(array(':pin'=> $pin));

	$rows = $stmt->fetchAll();

	$conn = null;
	
	return $rows;

}



/*
 * Validate Pin
 */
function validatePin($pin){

	$pinData = getPinData($pin);
	
		/** Invalid Session Id **/
	if(empty($pinData))
		die(returnMessage('Invalid Pin ', 480));
				
	if($pinData[0]['hasRedeemed'] == 1)
		die(returnMessage('Pin has been redeemed already!', 485));			
			
}


/**
 * Is this pin valid?
 */
function isPinValid($pin){

	$pinData = getPinData($pin);

	if(empty($pinData))
		return false;
				
	if($pinData[0]['hasRedeemed'] === 1)
		return false;		
				
	return true;
}

/*
 * Check a Pin
 */
function checkPin($pin){
	validatePin($pin);
	die(returnMessage("Pin Is Valid", 200));
}

/**
 * Redeem Pin
 */
function redeemPin($post){

	$username = $post['username'];
	$pin = $post['pin'];
	
	/** GET VALID PIN DATA, if pin is invalid we will die with an error **/
	validatePin($pin);


	$dbname     = "testDB";
	$conn      = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
	
	/** Set this pin to Redeemed **/
	$update  = "update donation_pins
				set hasRedeemed = 1, generateDate =  sysdate(3)
				where pin = :pin";
				
	$stmt   = $conn->prepare($update);


	$stmt->execute(array(
					'pin' => $pin
					));
					
	$stmt  = null;
	
	/** Insert pin for username **/
	$insert = "INSERT INTO `donators`(`username`, `pin`, `date`) VALUES (:username,:pin, sysdate(3))";
	$stmt   = $conn->prepare($insert);

	$stmt->execute(array(
					'username' => $username,
					'pin' => $pin
					));

	
	
	

 	$conn = null;
	
	die(returnMessage("success", 200));

}



function sendPM($memberId, $title, $body){
	global $ipbwi;
	
	$ipbwi->pm->sendAutomatedPm($memberId, 16, $title, $body);

}
/**
* Get donation items
*/
function getAllItems()
{
	$dbname     = "testDB";
	$conn      = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
	$select  = "SELECT productId, itemId, picture, name, cost, amount FROM donation_items";

	$stmt     = $conn->prepare($select);
	$stmt->execute();

	$rows = $stmt->fetchAll();

 $conn = null;
	die(json_encode($rows));
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
