<?php 
	ini_set('display_errors',1);
	ini_set('display_startup_errors',1);
	error_reporting(-1);

	require_once('ipbwi/ipbwi.inc.php');
	
	define('ipbwi_BOARD_PATH', '../forums');

	$servername = "localhost";
	$username = "root";
	$password = "rJCa!#7@mgq82hNS";
	$dbname = "testDB";
	
	$isLoggedIn = $ipbwi->member->isLoggedIn();
	$userInfo = $ipbwi->member->info();
	
?>
<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>donations</title>
    <link href="css/global.css" rel="stylesheet" type="text/css" />
</head>
<body>
<div class="container main-container">
    <header class="header-top">
        <div class="logo">
            <div class="logo-inner"></div>
        </div>
        <nav class="navigation">
            <a href="#" class="navigation-item">Home</a>
            <a href="#" class="navigation-item">Community</a>
            <a href="#" class="navigation-item play"></a>
            <a href="#" class="navigation-item">Vote</a>
            <a href="#" class="navigation-item active">Donations</a>
        </nav>
    </header>
    <div class="body-container" role="main">
          <div class="content-container clear-fix">
            <div class="left-container">
                <div class="box donation">
                    <header>
                        <h2>Donation Prizes 

		  </h2>
                    </header>
					<ul class="tab-links">

					</ul>
					<table class ="donationTable">
						<tr>
							<td class ="btn" align="center"><strong>Select</strong></td>
							<td class ="image"align="center"><strong>Image</strong></td>
							<td class ="item" align="center"><strong>Item Name</strong></td>
							<td class ="cost" align="center"><strong>Cost</strong></td>
						</tr>
					</table>
				</div>
            </div>
			<aside class="right-container">
                <div class="box">
                    <header>
                        <h2 class= "storePanel">Logged in as </h2>
                    </header>
						<ul class = "pointUL">  
						<?php 
							if($isLoggedIn){
								try {
										$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
										$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
										$select = "SELECT memberId, points FROM donation_users WHERE memberId = :memberId";
										$stmt = $conn->prepare($select);
										$stmt->execute(array(':memberId' => $userInfo['member_id']));

										$rows = $stmt->fetchAll();
										$num_rows = count($rows);
													
										if($num_rows > 0){
											foreach ($rows as $r) {
												echo "<li>Total: 0 </li>";
												echo "<li>Available Points: <strong class=\"apoint\">0"+ $r['points'] +"</strong></ </li>";
											}
										} else {
												echo "<li>Total: 0 </li>";
												echo "<li>Available Points: <strong class=\"apoint\">0</strong></li>";
										}
									} catch(PDOException $e) {
										echo "Error: " . $e->getMessage();
									}
									$conn = null;
							}
						?>				

						</ul>
                        <div class="clear-fix"></div>
                    </form>
                </div>
                <div class="button-links">
                    <a href="#">Purchase Points</a>
                    <a href="#">Payment History</a>
                    <a href="#">Redemption History</a>
                    <a href="#">Redeem Pin</a>
                    <a href="#">Gifting Center</a>
                </div>
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
<script src="js/util.js"></script>
<script src="js/AnguishDonationPage.js"></script>
<script>
	AnguishDonationPage.getInstance().init();
</script>
<script>
var data = <?php echo json_encode($ipbwi->member->info()); ?> ;

var isLoggedIn = false;
var loggedInAs = "";


if(data){
	loggedInAs = data.name;
	isLoggedIn = true;
} 
	

if(isLoggedIn){
	$('.storePanel').html("Logged in as "+loggedInAs);
} else {
	$('.storePanel').html("Not Logged in");
}
</script>

</body>
</html>