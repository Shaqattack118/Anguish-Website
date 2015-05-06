<?php 
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

define("TO_ROOT", "./");
define("ASSETS", TO_ROOT . "bin/php/");

$title = "Anguish - Vote Store";

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
            	<div class="box votingSites">
                    <header>
                        <h2 class="title">Voting Sites</h2>
                    </header>
                    <p>Would you like to help us expand Anguish? There is one simple way that will reward everyone including yourself! By voting for us every day not ony helps us become more popular and drive more players into our community, but it allows YOU to benefit from the amazing rewards we have set for you! So take a moment of your time and vote for us!</p>
               </div>
                <div class="box donation">
                    <header>
                        <h2 class="title">Voting Prizes</h2>
                    </header>
					<ul class="tab-links">

					</ul>
					<table class ="contentArea votingTable">
						<tr>
							<td colspan=4 class="tableHeading">Your Voting Auth Codes</td>
						</tr>
						<tr>
							<td class ="authcode" align="center"><strong>Auth Code</strong></td>
							<td class ="blank" align="center"><strong></strong></td>
							<td class ="status" align="center"><strong>Status</strong></td>
							<td class ="date" align="center"><strong>Date</strong></td>
						</tr>
            <tr>
              <td colspan=4 class="noAuthDate">No Auth Dates</td>
            </tr>
					</table>
      
      
        <table class ="contentArea votingTable">
						<tr>
							<td colspan=4 class="tableHeading">Your Statistics</td>
						</tr>
						<tr>
							<td class ="authcode" align="center"><strong>Total Votes</strong></td>
							<td class ="blank" align="center"><strong>0</strong></td>
						</tr>
					</table>
          
          
          	<table class ="contentArea votingTable">
						<tr>
							<td colspan=4 class="tableHeading">Votes By Site</td>
						</tr>
						<tr>
							<td class ="authcode" align="center"><strong>RuneLocus</strong></td>
							<td class ="blank" align="center"><strong>0</strong></td>
						</tr>
					</table>
				</div>

            </div>
			<aside class="right-container">
                <div class="button-links">
                	  <a class="purchasePoints">Vote History</a>
                	  <a class="purchasePointsHistory">Redeem History</a>
                	  <a class="redeemPin">Redeem Vote Auth</a>
                	  <a class="checkPin">Check Vote Auth</a>                   
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

<script src="js/AnguishVotePage.js"></script>
<script>vex.defaultOptions.className = 'vex-theme-os';</script>
<script>
	
	var isLoggedIn = <?=  (empty($isLoggedIn) ? "false" : $isLoggedIn)  ?>;
	var donatorPoints = <?= (empty($userInfo['donator_points_current']) ? 0 : $userInfo['donator_points_current']) ?>;
	var sessionId = '<?=  (empty($sessionId) ?  -1 : $sessionId) ?>';
	AnguishVotePage.getInstance().init(isLoggedIn, sessionId, donatorPoints);
</script>
</body>
</html>

