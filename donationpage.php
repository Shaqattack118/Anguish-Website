<?php 
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

define("TO_ROOT", "./");
define("ASSETS", TO_ROOT . "bin/php/");

$title = "Anguish - Donate";

require_once(ASSETS . 'data.php');
require_once(ASSETS . 'StringBuilder.php');

require_once(ASSETS . 'header.php');
require_once(ASSETS . 'footer.php');
require_once(TO_ROOT . 'ipbwi/ipbwi.inc.php');

$header->displayString();

$isLoggedIn = $ipbwi->member->isLoggedIn();
$userInfo = $ipbwi->member->info();
$ipbwi->session = new publicSessions();

$sessionId = $ipbwi->session->session_id;

?>
    <div class="body-container" role="main">
          <div class="content-container clear-fix">
            <div class="left-container">
                <div class="box donation">
                    <header>
                        <h2 class="title">Donation Prizes</h2>
                    </header>
					<ul class="tab-links">

					</ul>
					<table class ="contentArea donationTable">
						<tr>
							<td class ="btn" align="center"><strong>Select</strong></td>
							<td class ="item" align="center"><strong>Item Name</strong></td>
							<td class ="cost" align="center"><strong>Cost</strong></td>
						</tr>
					</table>
				</div>

            </div>
			<aside class="right-container">
                <div class="box">
                    <header>
					<?php
					if($isLoggedIn){  
						echo "<h2 class= \"storePanel\">Logged in as ".$userInfo['name']."</h2>";
					} else {
						echo "<h2 class= \"storePanel\">Logged in as Guest </h2>";
					}
					?>
                    </header>
						<ul class = "pointUL">  
						<?php 
							if($isLoggedIn){
								echo "<li>Available Points: <strong class=\"apoint\">". $userInfo['donator_points_current'] ."</strong></li>";
								echo "<li>Total Overall Points: " . $userInfo['donator_points_overall'] . "</li>";
								echo "<li><a href=\"#modal-one\" class=\"button cartBtn\">View Cart</a></li>";
							} else {
								echo "<li>Available Points: <strong class=\"apoint\">0</strong></li>";
								echo "<li>Total Overall Points: 0</li>";
							}
						?>				

						</ul>
                        <div class="clear-fix"></div>
                    </form>
                </div>
                <div class="button-links">
                	  <a class="purchasePoints">Purchase Points</a>
                	  <a class="purchasePointsHistory">Purchase History</a>
                	  <a class="redemptionCenter">Shop</a>
                    <a class="redemptionHistory">Shopping History</a>
                	  <a class="redeemPin">Redeem Pin</a>
                	  <a class="checkPin">Check Pin</a>

                    
                    
                </div>
            </aside>
		</div>
    
		
<div class="modal" id="modalNoticeAlert" aria-hidden="true">
  <div class="modal-dialog">
			<div class="box modalNotice">
	    	 <h3 id="heading"></h3>
	      	<p id="message"></p>
	      	<a href="#close" style="float: right" class="button close-btn" aria-hidden="true">close</a> 
	    <div class="clear-fix"></div>
	  	</div>
    </div>
 </div>                


<div class="modal hideSection" id="modal-three" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-header">
      <h2>Purchase Points <a href="#close" id="closeBtn" style="float: right" class="btn-close" aria-hidden="true">×</a> <!--CHANGED TO "#close"--></h2>
    </div>
    <div class="modal-body shoppingCart">
		<ul class="shoppingList">
	    </ul>
	 </div>
    <div class="modal-footer">
    </div>
   </div>
 </div>

<div class="modal" id="modal-one" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-header">
      <h2>Your Shopping Cart <a href="#close" style="float: right" class="btn-close" aria-hidden="true">×</a> <!--CHANGED TO "#close"--></h2>
    </div>
    <div class="modal-body shoppingCart">
			<ul class="shoppingList">
				<h2 class="emptyTxt">Your Shopping Cart is empty :( </h2>
	    	</ul>
			 <p style="float: right;"><strong>Total Points:</strong> <span class="totalAmt"> </span></p>
	   </div>
	    <div class="modal-footer">
	      <a href="#purchase" id="purchase" style="display: none;" class="button">Check Out</a>  <!--CHANGED TO "#close"-->
	    </div>
    </div>
</div>




 <?= $footer->displayString(); ?>

<script src="js/AnguishDonationPage.js"></script>
<script>vex.defaultOptions.className = 'vex-theme-os';</script>
<script>
	
	var isLoggedIn = <?=  (empty($isLoggedIn) ? "false" : $isLoggedIn)  ?>;
	var donatorPoints = <?= (empty($userInfo['donator_points_current']) ? 0 : $userInfo['donator_points_current']) ?>;
	var sessionId = '<?=  (empty($sessionId) ?  -1 : $sessionId) ?>';
	AnguishDonationPage.getInstance().init(isLoggedIn, sessionId, donatorPoints);
</script>
</body>
</html>

