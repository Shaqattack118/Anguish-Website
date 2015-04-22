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

$header->displayString();

?>
    <div class="body-container" role="main">
          <div class="content-container clear-fix">
            <div class="left-container">
                <div class="box donation">
                    <header>
                        <h2 id="title">Donation Prizes</h2>
                    </header>
				</div>

            </div>
			<aside class="right-container">

                <div class="button-links">
                	
                    <a href="#" id="bans">Bans</a>
                    <a href="#" id="mutes">Mutes</a>
                    <a href="#" id="logs">Logs</a>
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
<script>
	$(document).ready(function() {
		$("#bans").click(function() {
			$("#title").val("Bans");
		});	
	});
	
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script> 
<script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
</body>
</html>