<?php 
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

define("TO_ROOT", "../");
define("ASSETS", TO_ROOT . "bin/php/");

$title = "Anguish - Cpanel";

require_once(ASSETS . 'data.php');
require_once(ASSETS . 'StringBuilder.php');

require_once(ASSETS . 'header.php');
require_once(ASSETS . 'footer.php');
require_once(TO_ROOT . 'ipbwi/ipbwi.inc.php');

$isLoggedIn = $ipbwi->member->isLoggedIn();
$userInfo = $ipbwi->member->info();

if(!isLoggedIn) {
	header("Location: ../index.php");
}
if(!in_array($userInfo['member_group_id'], $staff_ranks)) {
	header("Location: ../index.php");
}
if(isset($_POST['submitbutton']) || isset($_GET['data'])) {
	
	if(isset($_POST['submitbutton'])) {
		$fdata = $_POST;
	} else {  
		$fdata = unserialize($_GET['data']);
		print_r($fdata);
	} 
	
	if(!isset($_GET['page'])) {
		$page = 1;
	} else {
		$page = $_GET['page'];
	}
	switch($fdata['submitbutton']) {
		case "Search Ban": 
			$query = "SELECT * FROM `banned` WHERE `username` = :uname LIMIT :start, :end";
			$pre = $conn->prepare($query);
			$start = ($page-1)*$resultsPerPage;
			$pre->bindParam(':start', $start, PDO::PARAM_INT);
			$pre->bindParam(':end', $resultsPerPage, PDO::PARAM_INT);
			$pre->bindParam(':uname', $fdata['sban'], PDO::PARAM_STR);
			$pre->execute();
			$results = $pre->fetchAll(PDO::FETCH_ASSOC);
			if(count($results) <= 0) {
				$data = 'The username you entered cannot be found!';
			} else {
				$data = "<table><tr><td>Username</td><td>Banned By</td><td>Date</td></tr>";
				for($i = 0; $i < count($results); $i++) {
					$data .= "<tr><td>{$results[$i]['username']}</td><td>{$results[$i]['bannedBy']}</td><td>{$results[$i]['date']}</td></tr>";
				}
				$data .= "</table>";
			}
			break;
		case "Search IP(Bans)": 
			$query = "SELECT * FROM `ipbans` WHERE `ip` = :ip LIMIT :start, :end";
			$pre = $conn->prepare($query);
			$start = ($page-1)*$resultsPerPage;
			$pre->bindParam(':start', $start, PDO::PARAM_INT);
			$pre->bindParam(':end', $resultsPerPage, PDO::PARAM_INT);
			$pre->bindParam(':ip', $fdata['siban'], PDO::PARAM_STR);
			$pre->execute();
			$max = 2;
			$results = $pre->fetchAll(PDO::FETCH_ASSOC);
			$serializedData = serialize($fdata);
			if(count($results) <= 0) {
				$data = 'The ip address you entered cannot be found!';
			} else {
				$data = "<table><tr><td>Ip Address</td><td>Userggname</td><td>Banned By</td><td>Date</td></tr>";
				for($i = 0; $i < count($results); $i++) {
					$data .= "<tr><td>{$results[$i]['ip']}</td><td>{$results[$i]['victim']}</td><td>{$results[$i]['bannedBy']}</td><td>{$results[$i]['date']}</td></tr>";
				}
				$data .= "</table><p>Current page: {$page}</p>
				<p>Go to page: <form method=\"get\"><input type=\"number\" name=\"page\" min=\"1\" max=\"{$max}\" value=\"{$page}.\">
				<input type=\"hidden\" name=\"data\" value=\"{$serializedData}\"><input type=\"submit\" name=\"action\" value=\"go\"></form></p>";
			}
			break;
		case "Ban User": 
			$data = "Ban user clicked";
			break;
		case "IP Ban User": 
			$data = "ip ban user clicked";
			break;
		case "Mac Ban User": 
			$data = "mac ban user clicked";
			break;
		case "Search Mac Bans": 
			$data = "search mac ban clicked";
			break;
			
			
		case "Search Mute": 
			$data = "This function is not working!";
			break;
		case "Search IP(Mutes)": 
			$data = "Search ip Mute clicked";
			break;
		case "Mute User": 
			$data = "This function is not working!";
			break;
		case "IP Mute User": 
			$data = "ip Mute user clicked";
			break;
	}
} else {
	$data = "Nothing to show!";
}
$header->displayString();

?>
    <div class="body-container" role="main">
          <div class="content-container clear-fix">
            <div class="left-container">
            	<div class="box">
                    <header>
                        <h2>Results</h2><h2 style="float:right; cursor:pointer;" id="hideorshow" onclick="hide();">Hide</h2>
                    </header>
                    <div id="resultsbody">
                    	<?php echo $data; ?>
                    </div>
				</div>
                <div class="box">
                    <header>
                        <h2 id="title"></h2>
                    </header>
                    <div id="centerbody">
                    	
                    </div>
				</div>

            </div>
			<aside class="right-container">

                <div class="button-links">
                    <a id="bans">Bans</a>
                    <a id="mutes">Mutes</a>
                    <a id="logs">Logs</a>
                </div>
                
	                <div id="banscontainer" style="visibility: hidden;">
	                	<div class="center">
	                		<form method="post">
	                			<?php
	                			if(in_array($userInfo['member_group_id'], $canViewLogs)) {
	                				echo '<p>Username: <input name="sban"></p>
		                			<p><input type="submit" name="submitbutton" value="Search Ban"></p>
		                			<p>Ip Address: <input name="siban"></p>
		                			<p><input type="submit" name="submitbutton" value="Search IP(Bans)"></p>
		                			<p>Mac Address: <input name="smban"></p>
		                			<p>Username: <input name="smban2"></p>
		                			<p><input type="submit" name="submitbutton" value="Search Mac Bans"></p>';
	                			} else {
									echo '<p>You don\'t have sufficient permissions to view logs!</p>';
								}
	                			
	                			if(in_array($userInfo['member_group_id'], $canPerfomActions)) {
	                				echo '<p>Username: <input></p>
		                			<p>Reason: <input></p>
		                			<p><input type="submit" name="submitbutton" value="Ban User"></p>
		                			<p>IP Address: <input></p>
		                			<p>Reason: <input></p>
		                			<p><input type="submit" name="submitbutton" value="IP Ban User"></p>
		                			<p>Mac Address: <input></p>
		                			<p>Reason: <input></p>
		                			<p><input type="submit" name="submitbutton" value="Mac Ban User"></p>';
	                			} else {
									echo 'You don\'t have sufficient permissions to perform these set of actions.';
								} ?>
                			</form>
	                	</div>
	                </div>
	                <div id="mutescontainer" style="visibility: hidden;">
	                	<div class="center">
	                		<form method="post">
	                			<?php
	                			
	                			if(in_array($userInfo['member_group_id'], $canViewLogs)) {
	                				echo '<p>Username: <input></p>
		                			<p><input type="submit" name="submitbutton" value="Search Mute"></p>
		                			<p>Ip Address: <input></p>
		                			<p><input type="submit" name="submitbutton" value="Search IP(Mutes)"></p>';
								} else {
									echo '<p>You don\'t have sufficient permissions to view logs!</p>';
								}
		                		if(in_array($userInfo['member_group_id'], $canPerfomActions)) {
		                			echo '<p>Username: <input></p>
		                			<p>Reason: <input></p>
		                			<p><input type="submit" name="submitbutton" value="Mute User"></p>
		                			<p>IP Address: <input></p>
		                			<p>Reason: <input></p>
		                			<p><input type="submit" name="submitbutton" value="IP Mute User"></p>';
		                		} else {
									echo 'You don\'t have sufficient permissions to perform these set of actions.';
								}?>
	                			
                			</form>
	                	</div>
	                </div>
	                <div id="logscontainer" style="visibility: hidden;">
	                	<div class="center">
	                		<form method="post">
	                			<?php
	                			if(in_array($userInfo['member_group_id'], $canViewLogs)) {
	                				echo '<p>Giver: <input></p>
	                				<p>Receiver: <input></p>
	                				<p><input type="submit" name="submitbutton" value="Search Trade Logs"></p>
	                				<p>Username: <input></p>
		                			<p><input type="submit" name="submitbutton" value="Search Drop Logs"></p>
		                			<p><input type="submit" name="submitbutton" value="Search Duel Logs"></p>
		                			<p>Username: <input></p>
		                			<p>Ip Address: <input></p>
		                			<p><input type="submit" name="submitbutton" value="Search Connection Logs"></p>';
								} else {
									echo '<p>You don\'t have sufficient permissions to view logs!</p>';
								}
	                			?>
		                		
                			</form>
	                	</div>
	                </div>
                </form>
            </aside>
		</div>
        <footer class="bottom-footer">
            <p class="copyright">
                <span>Designed by <a href="http://art0fray.deviantart.com" target="_blank">Ray</a></span>
                <br />
                <span>All Rights Reserved. © 2014 | Youserver.com</span>
            </p>
            <p class="links">
                <a href="#">home</a> | <a href="#">community</a> | <a href="#">play now</a> | <a href="#">vote</a> | <a href="#">donations</a></p>
        </footer>


  </div>
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
<script>
	$(document).ready(function() {
		$("#title").html("Bans");
		$("#centerbody").html($("#banscontainer").html());
		$("#bans").click(function() {
			$("#title").html("Bans");
			$("#centerbody").html($("#banscontainer").html());
		});	
		$("#mutes").click(function() {
			$("#title").html("Mutes");
			$("#centerbody").html($("#mutescontainer").html());
		});	
		$("#logs").click(function() {
			$("#title").html("Logs");
			$("#centerbody").html($("#logscontainer").html());
		});	
	});
	
	function hide() {
		if($("#hideorshow").html() == "Hide") {
			$("#resultsbody").css("visibility", "hidden");
			$("#resultsbody").css("height", "0px");
			$("#hideorshow").html("Show");
		} else {
			$("#resultsbody").css("visibility", "visible");
			$("#resultsbody").css("height", "inherit");
			$("#hideorshow").html("Hide");
		}
	}
	
</script>
</body>
</html>