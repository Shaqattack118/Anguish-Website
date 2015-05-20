<?php

/**
 * Anguish API 
 *
 **/

define("TO_ROOT", "./");
define("ASSETS", TO_ROOT . "bin/php/");

require_once(ASSETS . 'data.php');
require_once(ASSETS . 'StringBuilder.php');
require_once(TO_ROOT . 'ipbwi/ipbwi.inc.php');

$GLOBALS['pinArray'] = array(
					1 => 'Donator_Pin',
					2 => 'Super_Donator_Pin',
					3 => 'Five_Pack_Donator_Pin',
					4 => 'Drop_Pin',
					5 => 'Voting_Pin'
					);


	 
/** POST routes **/
if(isset($_POST) && !empty($_POST))
{

	$action = $_POST['action'];

	switch($action)
	{
		case 'purchase': purchase($_POST); return;
		case 'ipn' : ipn($_POST); return;
		case 'createVPin' : createVotePin($_POST); return;
		case 'redeemPin' :  redeemPin($_POST); return;
	}
}

/** Get routes **/
if(isset($_GET) && !empty($_GET))
{

	$action = $_GET['action'];

	switch($action)
	{
		case 'getRedemptionHistory': getRedemptionHistory(stripslashes($_GET['sessionId'])); break;
		case 'getPaymentHistory' : getPaymentHistory(stripslashes($_GET['sessionId'])); break;
		case 'checkPin': checkPin(stripslashes($_GET['pin'])); break;
		case 'getAllItems': getAllItems(); break;
		case 'getItems': getItems(stripslashes($_GET['category'])); break;
		case 'getVoteHistory' : getVoteHistory(stripslashes($_GET['sessionId'])); break;
	}

}



/** Get Tokens for member Id **/
function getTokens($memberId){
	global $conn;
	
  $select  = "SELECT donator_points_current FROM forums.members WHERE member_id = :memberId";
  
	$stmt   = $conn->prepare($select);
	$stmt->execute(array(':memberId'=> $memberId));

	$rows = $stmt->fetchAll();

	return $rows[0];
}


/**
 * Remove Points from a user
 */
function removePoints($memberId, $beforePoints, $points){
	global $conn;
	
  $select  = "UPDATE forums.members SET donator_points_current=? WHERE member_id=?";
  
  $newPoints = $beforePoints - $points;
  
	$stmt   = $conn->prepare($select);
	$stmt->execute(array($newPoints, $memberId));
	
}


/**
 * Give a player an Item
 */
function givePlayerItem($json){
	
	global $conn;
	
	$vals = array(
					'itemId' => $json['itemId'],
					'username' => $json['username'],
					'amount' => $json['amount']
					);
					
	$select  = "INSERT INTO testDB.donation_claim(`item_id`, `amount`, `claimed`, `username`,`createDate`)  VALUES (:itemId, :amount, 0 ,:username, sysdate(3))";
	$stmt   = $conn->prepare($select);

	$stmt->execute($vals);
 	
}

/**
 * Purchase History 
 */
function purchaseItemHistoryInsert($json){
	global $conn;
	
	$select  = "INSERT INTO testDB.donation_transactions(`memberId`, `username`, `productId`, `amount`, `price`, `transactionId`, `boughtdate`) VALUES (:memberId,:username,:productId,:amount,:price,:transactionId, sysdate(3))";
	$stmt   = $conn->prepare($select);

	$stmt->execute($json);
 	
}

/*
 * Get Redemption History
 */
function getRedemptionHistory($sessionId)
{
	global $conn;
	
	$memberId = getMemberIdBySessionId($sessionId);

	$select  = "	SELECT username,	productId, 	amount, 	price, 	boughtdate, 	transactionId FROM testDB.donation_transactions where memberId = :memberId order by boughtdate";

	$stmt     = $conn->prepare($select);
	$stmt->execute(array(':memberId'=> $memberId));

	$rows = $stmt->fetchAll();

	die(json_encode($rows));

}

/*
 * Get Vote History
 */
function getVoteHistory($sessionId)
{
	global $conn;
	
	$memberId = getMemberIdBySessionId($sessionId);

	$select  = "select vh.pin, dp.hasRedeemed, dp.generateDate from testDB.donation_pins dp, forums.vote_history vh  where vh.memberId = :memberId and vh.pin = dp.pin order by generateDate";

	$stmt     = $conn->prepare($select);
	$stmt->execute(array(':memberId'=> $memberId));

	$rows = $stmt->fetchAll();

	die(json_encode($rows));
}


/*
 * Get Payment History
 */
function getPaymentHistory($sessionId)
{
	global $conn;
	
	$memberId = getMemberIdBySessionId($sessionId);

	$select  = "	select ordernumber, productId, total, orderdate from testDB.donation_point_transactions where memberId = :memberId order by orderdate";

	$stmt     = $conn->prepare($select);
	$stmt->execute(array(':memberId'=> $memberId));

	$rows = $stmt->fetchAll();

	die(json_encode($rows));

}


/**
 * Select memberId by SessionId
 **/
function getMemberIdBySessionId($sessionId){
	global $conn;
	
  $select  = "SELECT member_id FROM forums.sessions where id = :sessionId";

	$stmt   = $conn->prepare($select);
	$stmt->execute(array(':sessionId'=> $sessionId));

	$rows = $stmt->fetchAll();

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


function createVotePin(){
					
	$pin = generateVotingPin();
	
	$pinData = array(
						'pin' => $pin,
						'hasRedeemed' => 0,
						'type' => 3
					);
			
	insertIntoPinTable($pinData);
			
	die(json_encode($pinData));
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
		case 'Vote_Pin':
				
					$pin = generateVotingPin();
					
					$pinData = array(
						'pin' => $pin,
						'hasRedeemed' => 0,
						'type' => 3
					);
			
				insertIntoPinTable($pinData);
			
			die(json_encode($pinData));
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
	
	global $conn;
	
	$select  = "INSERT INTO testDB.donation_pins(`pin`, `hasRedeemed`, `generateDate`, `type`) VALUES (:pin,:hasRedeemed,sysdate(3),:type)";
	$stmt   = $conn->prepare($select);


	$stmt->execute($json);

 	
}


function getPinData($pin){
	global $conn;
	
	$select  = "SELECT pin, generateDate, hasRedeemed FROM testDB.donation_pins WHERE pin = :pin ";
	$stmt   = $conn->prepare($select);

	$stmt->execute(array(':pin'=> $pin));

	$rows = $stmt->fetchAll();

	return $rows;

}



/*
 * Validate Pin
 */
function validatePin($pin){

	$pinData = getPinData($pin);
	
		/** Invalid Session Id **/
	if(empty($pinData))
		die(returnMessage(' is Invalid!', 480));
				
	if($pinData[0]['hasRedeemed'] == 1)
		die(returnMessage(' has been Redeemed Already!', 485));			
			
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
	die(returnMessage(" Is Valid", 200));
}

/**
 * Redeem Pin
 */
function redeemPin($post){
	global $conn;
	
	$username = $post['username'];
	$pin = $post['pin'];
	
	/** GET VALID PIN DATA, if pin is invalid we will die with an error **/
	validatePin($pin);

	/** Set this pin to Redeemed **/
	$update  = "update testDB.donation_pins set hasRedeemed = 1, generateDate =  sysdate(3)	where pin = :pin";
				
	$stmt   = $conn->prepare($update);


	$stmt->execute(array( 'pin' => $pin ));
					
	/** Insert pin for username **/
	$insert = "INSERT INTO testDB.donators(`username`, `pin`, `date`) VALUES (:username,:pin, sysdate(3))";
	$stmt   = $conn->prepare($insert);

	$stmt->execute(array(
					'username' => $username,
					'pin' => $pin
					));


	die(returnMessage("success", 200));

}


/*
 * Automated PM from Automated Sender 
 */
function sendPM($memberId, $title, $body){
	global $ipbwi;
	
	$ipbwi->pm->sendAutomatedPm($memberId, 16, $title, $body);

}
/**
* Get donation items
*/
function getAllItems()
{
	
	global $conn;
		
	$select  = "SELECT productId, itemId, picture, name, cost, amount FROM testDB.donation_items";

	$stmt     = $conn->prepare($select);
	$stmt->execute();

	$rows = $stmt->fetchAll();

	die(json_encode($rows));
}


/**
* Get donation items
*/
function getItems($json)
{
	global $conn;
	
	$select  = "SELECT productId, itemId, picture, name, cost, amount FROM testDB.donation_items WHERE category = :category";

	$stmt     = $conn->prepare($select);
	$stmt->execute(array(':category'=> $json));

	$rows = $stmt->fetchAll();
	die(json_encode($rows));
}

/*
 * Get Donation Items
 */
function getItemsById($productIds)
{
	global $conn;
	
	$select  = "SELECT productId, itemId, picture, name, cost, amount FROM testDB.donation_items WHERE productId IN (".str_pad('',count($productIds)*2-1,'?,').");";

	$stmt    = $conn->prepare($select);
	$stmt->execute($productIds);

	$rows = $stmt->fetchAll();


	return $rows;
	

}

/** 
 * Return JSON to Front-End
 */
function returnMessage($message, $code){
	return json_encode(array(
					'Message' => $message,
					'Code' => $code
					));
	
}

/** Generate a pin with a $prefix of 9 length **/
function generatePin($prefix){
	return generateString($prefix, 9);
}

/** Generate Donator Pin **/
function generateDonatorPin(){
	return generatePin("D");
}

/** Generate SuperDonator Pin **/
function generateSuperDonatorPin(){
	return generatePin("SD");
}

/** Generate Drop Pin **/
function generateDropPin(){
	return generatePin("DR");
}

/** Generate VOTING Pin **/
function generateVotingPin(){
	return generatePin("V");
}


/** Generate Transaction ID **/
function generateTransactionId(){
	return generateString("TRAN_", 10);
}


?>
