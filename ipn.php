<?php 

error_reporting(E_ALL);
ini_set('display_errors', 'On');

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
   

					
	function getMemberInfoBySessionId($conn, $sessionId){


		$select  = "SELECT member_id , donator_points_current FROM `sessions` s, `members` m where s.id = :sessionId and s.member_id = m.member_id";
	 

		$stmt = $conn->prepare($select);
		$stmt->execute(array(':sessionId'=> $sessionId));

		$rows = $stmt->fetchAll();

										
		return $rows;
	}

				
	function addPoints($sessionId, $points){
			
		$dbname  = "forums";
		$conn    = new PDO("mysql:host=".servername.";dbname=$dbname", username, password);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		
		$rows = getMemberInfoBySessionId($sessionId);
		
		
		/** Invalid Session Id **/
		if(empty($rows)){
			$conn = null;
			die('Invalid Session Id'));
		}
		
		$row = $rows[0];
					
		$points = $row['donator_points_current'];
		$memberId = $row['member_id'];
		
		$dbname   = "forums";

		$select  = "UPDATE members 
				   SET donator_points_current=?
				   WHERE member_id=?";
	  
		$stmt  = $conn->prepare($select);
		$stmt->execute(array($points ,$memberId));
		
	}



		
		
	function processTransaction($data){

		$fp = fopen('transactions.txt', 'a');


		$bmtProducts = array(
			'91390007' => '50',
			'91390006' => '10',
			'91390005' => '25'
		);
	   
	   $sessionId = $data['ccom'];
	   $pid = $data['productid'];
	   
	   $value = $bmtProducts[$pid];
	   
	   $transactionDetails = encode_json 
	   addPoints($sessionId, $value);
	  
	  
		$fp = fopen('transactions.txt', 'a');
		fwrite($fp, $sessionId);
		fwrite($fp, $value);
		fwrite($fp, json_encode($data));
		fclose($fp);
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

	   $keycount = $bmtparser->getElement ('keycount');

	   $data = $bmtparser->tag_data;
	  
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
?>