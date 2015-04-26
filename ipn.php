<?php 

/**
 * BMTMicro IPN, connects and adds points to a ipboard member
 *
 */

ini_set('display_errors','On');
error_reporting(E_ALL);

define('servername', 'localhost');
define('username', 'root');
define('password', 'rJCa!#7@mgq82hNS');

	   
	   
class BMTXMLParser {
   var $tag_name;
   var $tag_data;
   var $tag_prev_name;
   var $tag_parent_name;
   function BMTXMLParser () {
       $tag_name = NULL;
       $tag_data = array ();
       $tag_prev_name = NULL;
       $tag_parent_name = NULL;
       }
   function startElement ($parser, $name, $attrs) {
      if ($this->tag_name != NULL) {
         $this->tag_parent_name = $this->tag_name;
         }                
      $this->tag_name = $name;
      }
   function endElement ($parser, $name) {
      if ($this->tag_name == NULL) {
         $this->tag_parent_name = NULL;
         }
      $this->tag_name = NULL;
      $this->tag_prev_name = NULL;
      }
   function characterData ($parser, $data) {
      if ($this->tag_name == $this->tag_prev_name) {
         $data = $this->tag_data[$this->tag_name] . $data;
         }
      $this->tag_data[$this->tag_name] = $data;
      if ($this->tag_parent_name != NULL) {
         $this->tag_data[$this->tag_parent_name . "." . $this->tag_name] = $data;
         }
      $this->tag_prev_name = $this->tag_name;
      }
   function parse ($data) {
      $xml_parser = xml_parser_create ();
      xml_set_object ($xml_parser, $this);                        
      xml_parser_set_option ($xml_parser, XML_OPTION_CASE_FOLDING, false);
      xml_set_element_handler ($xml_parser, "startElement", "endElement");
      xml_set_character_data_handler ($xml_parser, "characterData");
      $success = xml_parse ($xml_parser, $data, true);
      if (!$success) {
          $this->tag_data['error'] =  sprintf ("XML error: %s at line %d", xml_error_string(xml_get_error_code ($xml_parser)), xml_get_current_line_number ($xml_parser));
          }
      xml_parser_free ($xml_parser);
      return ($success);
      }
   function getElement ($tag) {
      return ($this->tag_data[$tag]);
      }
 }		


	echo '<?xml version="1.0" encoding="utf-8"?>';
	echo '<response>';
	echo '<registrationkey>';
	
	$bmtparser = new BMTXMLParser ();
	if ($bmtparser->parse(file_get_contents('php://input'))) {   
	   # keycount is normally 1. However, if the product option "Use one
	   # key" in the vendor area has been unchecked, BMT Micro expects
	   # you to send back as many keys as the number of items (quantity)
	   # ordered. The variable keycount represents thef number of keys
	   # that the system expects to receive back from you.

	   $keycount = $bmtparser->getElement ('keycount');

		$data = $bmtparser->tag_data;
		
		/** Process the transaction **/
		processTransaction($data);
	  
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
	

	/**
	 * Build Transaction History 
	 *
	 **/
	function transactionHistory($memberId, $data){
	
		// build our transaction history 
		$transactionHistory = array(
			'orderId' => $data['orderid'],
			'ordernumber' => $data['ordernumber'],
			'productid' => $data['productid'],
			'firstname' => $data['firstname'],
			'lastname' => $data['lastname'],
			'address1' => $data['address1'],
			'city' => $data['city'],
			'state' => $data['state'],
			'zip' => $data['zip'],
			'country' => $data['country'],
			'phone' => $data['phone'],
			'ipaddress' => $data['ipaddress'],
			'emailAddress' => $data['email'],
			'total' => $data['vendorroyalty'],
			'orderdate' => $data['orderdate'],
			'memberId' => $memberId
		);
		

		$dbname     = "testDB";
		$conn      = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
		$select  = "INSERT INTO `donation_point_transactions`(`orderId`, `ordernumber`, `memberId`,`productid`, `firstname`, `lastname`, `address1`, `city`, `state`, `zip`, `country`, `phone`, `ipaddress`, `emailAddress`, `total`, `orderdate`, `processdate` ) VALUES (:orderId, :ordernumber, :memberId, :productid, :firstname, :lastname, :address1, :city, :state, :zip, :country, :phone, :ipaddress, :emailAddress, :total, :orderdate, sysdate(3))";
		$stmt   = $conn->prepare($select);

			
		$stmt->execute($transactionHistory);

	}
	
	/**
	 * Add Points to user
	 *
	 */
	function addPoints($data, $rows, $value){

	
		$dbname  = "forums";
		$conn    = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$row = $rows[0];
		$donator_points_overall = $row['donator_points_overall'] + $value;
		$points = $row['donator_points_current'] + $value; // increase our new vlaue 
		$memberId = $row['member_id'];

		$select  = 'UPDATE members SET donator_points_current=?, donator_points_overall = ?  WHERE member_id=?';
	  
		$stmt  = $conn->prepare($select);
		$stmt->execute(array($points , $donator_points_overall, $memberId));

			
		transactionHistory($memberId, $data);
	}

   /*
    * Process a transaction
	*/
	function processTransaction($data){

		$bmtProducts = array( // todo add more products 
			91390007 => '50',
			91390006 => '10',
			91390005 => '25',
			91390009 => '100',
			91390008 => '75',
			
		);
	   
	   $sessionId = $data['ccom'];
	   $pid = $data['productid'];
	   
	   $value = $bmtProducts[$pid];
	   
	 	$file = 'people.log';
		$person = "".json_encode($data)."\n";

		file_put_contents($file, $person, FILE_APPEND | LOCK_EX);
		
		/** get member info by session ID **/
		$rows = getMemberInfoBySessionId($sessionId);
		addPoints($data, $rows, $value);

		
	}
	
?>