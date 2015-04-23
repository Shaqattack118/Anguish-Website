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

//Staff ranks who can access the cpanel
$staff_ranks = array(4,7,6,8);
//Staff ids for who can view bans, mutes and other logs
$canViewLogs = array(4,7,6);
//Staff ids for who can ban users, mute etc.
$canPerfomActions  = array(4,7,6);

?>