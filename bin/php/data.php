<?php
$servername = "localhost";
$username = "root";
$password = "rJCa!#7@mgq82hNS";
$dbname = "testDB";
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
										$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

										
										
define('servername', 'localhost');
define('username', 'root');
define('password', 'rJCa!#7@mgq82hNS');



$navigation = array(
//array(name, url)
array("products", "http://anguishps.com/website/donationpage.php"),
array("Forum", "http://anguishps.com/forums/"),
array("", "#"),
array("Vote", "#"),
array("HiScores", "#"),
);

					
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
   
//Staff ranks who can access the cpanel
$staff_ranks = array(4,7,6,8);
//Staff ids for who can view bans, mutes and other logs
$canViewLogs = array(4,7,6);
//Staff ids for who can ban users, mute etc.
$canPerfomActions  = array(4,7,6);

?>