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

if(!$isLoggedIn) {
	header("Location: ../index.php");
}
if(!in_array($userInfo['member_group_id'], $staff_ranks)) {
	header("Location: ../index.php");
}
if(isset($_POST['submitbutton']) || isset($_POST['data'])) {
	$dontSearch = 'DONTSEARCHUSERNAMES';
	if(isset($_POST['submitbutton'])) {
		$fdata = $_POST;
	} else {  
		$fdata = unserialize(urldecode($_POST['data']));
	} 
	
	if(!isset($_GET['page'])) {
		$page = 1;
	} else {
		$page = $_GET['page'];
	}
	if(!isset($_POST['page'])) {
		$page = 1;
	} else {
		$page = $_POST['page'];
	}
	switch($fdata['submitbutton']) {
		case "Search Ban":
			$load = 1;
			$table = 'banned';
			$query = "SELECT * FROM `{$table}` WHERE `username` = :uname";
			$pre = $conn->prepare($query);
			$start = ($page-1)*$resultsPerPage;
			$pre->bindParam(':uname', $fdata['sban'], PDO::PARAM_STR);
			$pre->execute();
			$results = $pre->fetchAll(PDO::FETCH_ASSOC);
			if(count($results) <= 0) {
				$data = 'The username you entered cannot be found!';
				$page = 0;
			} else {
				$data = "<table class=\"contentArea donationTable\"><tr class=\"row\"><td>Username</td><td>Banned By</td><td>Date</td></tr>";
				for($i = 0; $i < $resultsPerPage; $i++) {
					if(!empty($results[($i+($resultsPerPage*($page-1)))]['username'])) 
						$data .= "<tr class=\"row\"><td>{$results[($i+($resultsPerPage*($page-1)))]['username']}</td><td>{$results[($i+($resultsPerPage*($page-1)))]['bannedBy']}</td><td>{$results[($i+($resultsPerPage*($page-1)))]['date']}</td></tr>";
				}
				$data .= "</table>";
			}
			break;
		case "Search IP(Bans)": 
			$load = 1;
			$table = 'ipbans';
			$query = "SELECT * FROM `{$table}` WHERE `ip` = :ip or `victim` = :usrname";
			$pre = $conn->prepare($query);
			$start = ($page-1)*$resultsPerPage;
			$pre->bindParam(':ip', $fdata['siban'], PDO::PARAM_STR);
			if(!empty($fdata['siban2'])) {
				$pre->bindParam(':usrname', $fdata['siban2'], PDO::PARAM_STR);
			} else {
				$pre->bindParam(':usrname', $dontSearch, PDO::PARAM_STR);
			}
			$pre->execute();
			$results = $pre->fetchAll(PDO::FETCH_ASSOC);
			if(count($results) <= 0) {
				$data = 'The ip address you entered cannot be found!';
				$page = 0;
			} else {
				$data = "<table class=\"contentArea donationTable\"><tr class=\"row\"><td>Ip Address</td><td>Username</td><td>Banned By</td><td>Date</td></tr>";
				for($i = 0; $i < $resultsPerPage; $i++) {
					if(!empty($results[($i+($resultsPerPage*($page-1)))]['ip'])) 
						$data .= "<tr class=\"row\"><td>{$results[($i+($resultsPerPage*($page-1)))]['ip']}</td><td>{$results[($i+($resultsPerPage*($page-1)))]['victim']}</td><td>{$results[($i+($resultsPerPage*($page-1)))]['bannedBy']}</td><td>{$results[($i+($resultsPerPage*($page-1)))]['date']}</td></tr>";
				}

			}
			break;
		case "IP Ban User":
			$load = 1;
			$page = 0;
			$table = 'ipbans';
			$today = date("Y-m-d H:i:s");
			$query = "INSERT INTO `{$table}`(`ip`, `bannedBy`, `date`) VALUES (?,?,?)";
			$pre = $conn->prepare($query);
			$pre->execute(array($fdata['ipban'], $userInfo['name'], $today));
			$data = $fdata['ipban'] . " ip banned successfully!";
			break;
		case "Ban User": 
			$load = 1;
			$page = 0;
			$table = 'banned';
			$today = date("Y-m-d H:i:s");
			$query = "INSERT INTO `{$table}`(`username`, `bannedBy`, `date`) VALUES (?,?,?)";
			$pre = $conn->prepare($query);
			$pre->execute(array($fdata['uban'], $userInfo['name'], $today));
			$data = $fdata['uban'] . " banned successfully!";
			break;
		case "Mac Ban User":
			$load = 1;
			$page = 0;
			$table = 'macbans';
			$today = date("Y-m-d H:i:s");
			$query = "INSERT INTO `{$table}`(`mac`, `bannedBy`, `date`) VALUES (?,?,?)";
			$pre = $conn->prepare($query);
			$pre->execute(array($fdata['macban'], $userInfo['name'], $today));
			$data = $fdata['macban'] . " mac addess banned successfully!";
			break;
		case "Search Mac Bans": 
			$load = 1;
			$table = 'macbans';
			$query = "SELECT * FROM `{$table}` WHERE `mac` = :mac or `victim`= :usname";
			$pre = $conn->prepare($query);
			$start = ($page-1)*$resultsPerPage;
			$pre->bindParam(':mac', $fdata['smban'], PDO::PARAM_STR);
			if(!empty($fdata['smban2'])) {
				$pre->bindParam(':usname', $fdata['smban2'], PDO::PARAM_STR);
			} else {
				$pre->bindParam(':usname', $dontSearch, PDO::PARAM_STR);
			}
			$pre->execute();
			$results = $pre->fetchAll(PDO::FETCH_ASSOC);
			if(count($results) <= 0) {
				$data = 'The mac address you entered cannot be found!';
				$page = 0;
			} else {
				$data = "<table class=\"contentArea donationTable\"><tr class=\"row\"><td>Mac Address</td><td>Username</td><td>Banned By</td><td>Date</td></tr>";
				for($i = 0; $i < $resultsPerPage; $i++) {
					if(!empty($results[($i+($resultsPerPage*($page-1)))]['mac'])) 
						$data .= "<tr class=\"row\"><td>{$results[($i+($resultsPerPage*($page-1)))]['mac']}</td><td>{$results[($i+($resultsPerPage*($page-1)))]['victim']}</td><td>{$results[($i+($resultsPerPage*($page-1)))]['bannedBy']}</td><td>{$results[($i+($resultsPerPage*($page-1)))]['date']}</td></tr>";
				}

			}
			break;
			break;
			
			
		case "Search Mute":
			$load = 2;
			$page = 0;
			$data = "This function is not working!";
			break;
		case "Search IP(Mutes)": 
			$load = 2;
			$table = 'ipmutes';
			$query = "SELECT * FROM `{$table}` WHERE `ip` = :ip";
			$pre = $conn->prepare($query);
			$start = ($page-1)*$resultsPerPage;
			$pre->bindParam(':ip', $fdata['simute'], PDO::PARAM_STR);
			$pre->execute();
			$results = $pre->fetchAll(PDO::FETCH_ASSOC);
			if(count($results) <= 0) {
				$data = 'The ip address you entered cannot be found!';
				$page = 0;
			} else {
				$data = "<table class=\"contentArea donationTable\"><tr class=\"row\"><td>Ip Address</td><td>Username</td><td>Muted By</td><td>Date</td></tr>";
				for($i = 0; $i < $resultsPerPage; $i++) {
					if(!empty($results[($i+($resultsPerPage*($page-1)))]['ip'])) 
						$data .= "<tr class=\"row\"><td>{$results[($i+($resultsPerPage*($page-1)))]['ip']}</td><td>{$results[($i+($resultsPerPage*($page-1)))]['victim']}</td><td>{$results[($i+($resultsPerPage*($page-1)))]['bannedBy']}</td><td>{$results[($i+($resultsPerPage*($page-1)))]['date']}</td></tr>";
				}

			}
			break;
		case "Mute User": 
			$load = 2;
			$page = 0;
			$data = "This function is not working!";
			break;
		case "IP Mute User": 
			$load = 2;
			$page = 0;
			$table = 'ipmutes';
			$today = date("Y-m-d H:i:s");
			$query = "INSERT INTO `{$table}`(`ip`, `bannedBy`, `date`) VALUES (?,?,?)";
			$pre = $conn->prepare($query);
			$pre->execute(array($fdata['ipmute'], $userInfo['name'], $today));
			$data = $fdata['ipmute'] . " ip muted successfully!";
			break;
		case "Search Trade Logs":
			$load = 3;
			$table = 'tradelogs';
			$query = "SELECT * FROM `{$table}` WHERE `username` = :user or `tradewith` = :twith";
			$pre = $conn->prepare($query);
			$start = ($page-1)*$resultsPerPage;
			$pre->bindParam(':user', $fdata['stl'], PDO::PARAM_STR);
			$pre->bindParam(':twith', $fdata['stl2'], PDO::PARAM_STR);
			$pre->execute();
			$results = $pre->fetchAll(PDO::FETCH_ASSOC);
			if(count($results) <= 0) {
				$data = 'The username you entered cannot be found!';
				$page = 0;
			} else {
				$data = "<table class=\"contentArea donationTable\"><tr class=\"row\"><td>Giver</td><td>Item</td><td>Amount</td><td>Receiver</td><td>Date</td><td>Type</td></tr>";
				for($i = 0; $i < $resultsPerPage; $i++) {
					if(!empty($results[($i+($resultsPerPage*($page-1)))]['username'])) 
						$data .= "<tr class=\"row\"><td>{$results[($i+($resultsPerPage*($page-1)))]['username']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['itemname']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['amountreceive']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['tradewith']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['date']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['type']}</td>
						</tr>";
				}
			}
			break;
		case "Search Drop Logs":
			$load = 3;
			$table = 'droplog';
			$query = "SELECT * FROM `{$table}` WHERE `playername` = :user";
			$pre = $conn->prepare($query);
			$start = ($page-1)*$resultsPerPage;
			$pre->bindParam(':user', $fdata['sdl'], PDO::PARAM_STR);
			$pre->execute();
			$results = $pre->fetchAll(PDO::FETCH_ASSOC);
			if(count($results) <= 0) {
				$data = 'The username you entered cannot be found!';
				$page = 0;
			} else {
				$data = "<table class=\"contentArea donationTable\"><tr class=\"row\"><td>Username</td><td>ItemId</td><td>Amount</td><td>Date</td></tr>";
				for($i = 0; $i < $resultsPerPage; $i++) {
					if(!empty($results[($i+($resultsPerPage*($page-1)))]['playername'])) 
						$data .= "<tr class=\"row\"><td>{$results[($i+($resultsPerPage*($page-1)))]['playername']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['itemid']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['amount']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['time']}</td>
						</tr>";
				}

			}
			break;
		case "Search Connection Logs":
			$load = 3;
			$table = 'connections';
			$query = "SELECT * FROM `{$table}` WHERE `name` = :user or `ip` = :ip";
			$pre = $conn->prepare($query);
			$start = ($page-1)*$resultsPerPage;
			$pre->bindParam(':user', $fdata['scl'], PDO::PARAM_STR);
			if(!empty($fdata['scl2'])) {
				$pre->bindParam(':ip', $fdata['scl2'], PDO::PARAM_STR);
			} else {
				$pre->bindParam(':ip', $dontSearch, PDO::PARAM_STR);
			}
			$pre->execute();
			$results = $pre->fetchAll(PDO::FETCH_ASSOC);
			if(count($results) <= 0) {
				$data = 'The username you entered cannot be found!';
				$page = 0;
			} else {
				$data = "<table class=\"contentArea donationTable\"><tr class=\"row\"><td>Username</td><td>ip</td><td>Mac</td><td>Date</td></tr>";
				for($i = 0; $i < $resultsPerPage; $i++) {
					if(!empty($results[($i+($resultsPerPage*($page-1)))]['name'])) 
						$data .= "<tr class=\"row\"><td>{$results[($i+($resultsPerPage*($page-1)))]['name']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['ip']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['mac']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['time']}</td>
						</tr>";
				}

			}
			break;
		case "Search Duel Logs":
			$load = 3;
            $table = 'duellogs';
			$query = "SELECT * FROM `{$table}` WHERE `winner` = :user or `loser` = :user";
			$pre = $conn->prepare($query);
			$start = ($page-1)*$resultsPerPage;
			$pre->bindParam(':user', $fdata['sdl'], PDO::PARAM_STR);
			$pre->execute();
			$results = $pre->fetchAll(PDO::FETCH_ASSOC);
			if(count($results) <= 0) {
				$data = 'The username you entered cannot be found!';
				$page = 0;
			} else {
				$data = "<table class=\"contentArea donationTable\"><tr class=\"row\"><td>Winner</td><td>Loser</td><td>Item</td><td>Amount</td><td>Type</td><td>Date</td></tr>";
				for($i = 0; $i < $resultsPerPage; $i++) {
					if(!empty($results[($i+($resultsPerPage*($page-1)))]['winner']))
						$data .= "<tr class=\"row\"><td>{$results[($i+($resultsPerPage*($page-1)))]['winner']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['loser']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['itemname']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['amount']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['type']}</td>
						<td>{$results[($i+($resultsPerPage*($page-1)))]['date']}</td>
						</tr>";
				}

			}
			break;
	}
	if($page > 0) {
		$serializedData = urlencode(serialize($fdata));
		$max = count($results);
		$max= ceil($max/$resultsPerPage);
		
		$data .= "</table><div class=\"content-container\"><p>Current page: {$page}</p>
		<p>Go to page: <form method=\"post\"><input type=\"number\" name=\"page\" min=\"1\" max=\"{$max}\" value=\"{$page}\">
		<input type=\"hidden\" name=\"data\" value=\"{$serializedData}\"><input type=\"submit\" name=\"action\" value=\"go\"></form></p></div>";
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
						<h2>Results<span style="float:right; cursor:pointer;" id="hideorshow" onclick="hide();">Hide</span></h2>
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
		                			<p>Username: <input name="siban2"></p>
		                			<p><input type="submit" name="submitbutton" value="Search IP(Bans)"></p>
		                			<p>Mac Address: <input name="smban"></p>
		                			<p>Username: <input name="smban2"></p>
		                			<p><input type="submit" name="submitbutton" value="Search Mac Bans"></p>';
	                			} else {
									echo '<p>You don\'t have sufficient permissions to view logs!</p>';
								}
	                			
	                			if(in_array($userInfo['member_group_id'], $canPerfomActions)) {
	                				echo '<p>Username: <input name="uban"></p>
		                			<p><input type="submit" name="submitbutton" value="Ban User"></p>
		                			<p>IP Address: <input name="ipban"></p>
		                			<p><input type="submit" name="submitbutton" value="IP Ban User"></p>
		                			<p>Mac Address: <input name="macban"></p>
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
		                			<p>Ip Address: <input name="simute"></p>
		                			<p><input type="submit" name="submitbutton" value="Search IP(Mutes)"></p>';
								} else {
									echo '<p>You don\'t have sufficient permissions to view logs!</p>';
								}
		                		if(in_array($userInfo['member_group_id'], $canPerfomActions)) {
		                			echo '<p>Username: <input></p>
		                			<p><input type="submit" name="submitbutton" value="Mute User"></p>
		                			<p>IP Address: <input name="ipmute"></p>
		                			<p><input type="submit" name="submitbutton" value="IP Mute User"></p>';
		                		} else {
									echo 'You don\'t have sufficient permissions to perform these set of actions.';
								}
								?>
                			</form>
	                	</div>
	                </div>
	                <div id="logscontainer" style="visibility: hidden;">
	                	<div class="center">
	                		<form method="post">
	                			<?php
	                			if(in_array($userInfo['member_group_id'], $canViewLogs)) {
	                				echo '<p>Giver: <input name="stl"></p>
	                				<p>Receiver: <input name="stl2"></p>
	                				<p><input type="submit" name="submitbutton" value="Search Trade Logs"></p>
	                				<p>Username: <input name="sdl"></p>
		                			<p><input type="submit" name="submitbutton" value="Search Drop Logs"></p>
		                			<p><input type="submit" name="submitbutton" value="Search Duel Logs"></p>
		                			<p>Username: <input name="scl"></p>
		                			<p>Ip Address: <input name="scl2"></p>
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
		var switchs = <?php if(!empty($load)) echo $load; else echo 1; ?>;
		switch(switchs) {
			case 1:
				$("#title").html("Bans");
				$("#centerbody").html($("#banscontainer").html());
				break;
			case 2:
				$("#title").html("Mutes");
				$("#centerbody").html($("#mutescontainer").html());
				break;
			case 3:
				$("#title").html("Logs");
				$("#centerbody").html($("#logscontainer").html());
				break;
		}
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
