<?php 
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);

define("TO_ROOT", "./");
define("ASSETS", TO_ROOT . "bin/php/");

$title = "Anguish RSPS - Homepage";

require_once(ASSETS . 'data.php');
require_once(ASSETS . 'StringBuilder.php');

require_once(ASSETS . 'header.php');
require_once(ASSETS . 'footer.php');
require_once(TO_ROOT . 'ipbwi/ipbwi.inc.php');

$header->displayString();
?>
    <div class="body-container" role="main">
    		<div class="content-container clear-fix">
            <div class="center-container">
                <div class="box notice">
                    <h3>NOTICE!</h3>
                    <p>testing git pull
                    </p>
                    <div class="clear-fix"></div>
         				 </div>
						</div>
				</div>
        <div class="slider-container clear-fix">
            <div class="slider">
                <div class="slides">
                    <div class="slide">
                        <div class="text-content">
                            <h2>Lorem Ipsum</h2>
                            <p>Fusce vitae sagittis arcu ut nec quam iaculis, malesuada metus in.</p>
                        </div>
                        <img src="img/slides/first.jpg" alt="" />
                    </div>
                    <div class="slide">
                        <div class="text-content">
                            <h2>Lorem Ipsum 1</h2>
                            <p>Fusce vitae sagittis arcu ut nec quam iaculis, malesuada metus in.</p>
                        </div>
                        <img src="img/slides/first.jpg" alt="" />
                    </div>
                    <div class="slide">
                        <div class="text-content">
                            <h2>Lorem Ipsum 2</h2>
                            <p>Fusce vitae sagittis arcu ut nec quam iaculis, malesuada metus in.</p>
                        </div>
                        <img src="img/slides/first.jpg" alt="" />
                    </div>
                    <div class="slide">
                        <div class="text-content">
                            <h2>Lorem Ipsum 3</h2>
                            <p>Fusce vitae sagittis arcu ut nec quam iaculis, malesuada metus in.</p>
                        </div>
                        <img src="img/slides/first.jpg" alt="" />
                    </div>
                </div>
            </div>
            <aside class="slider-side">
                <figure class="active">
                    <img src="img/slides/first.jpg" alt="" />
                </figure>
                <figure>
                    <img src="img/slides/first.jpg" alt="" />
                </figure>
                <figure>
                    <img src="img/slides/first.jpg" alt="" />
                </figure>
                <figure>
                    <img src="img/slides/first.jpg" alt="" />
                </figure>
            </aside>
        </div>
        <div class="content-container clear-fix">
            <div class="center-container">
                <div class="box">
                    <header>
                        <h2>Welcome to Anguish</h2>
                    </header>
                    <figure>
                        <img src="img/thumb_1.jpg" alt="" />
                    </figure>
                    <p>Welcome to the Anguish PS website. 
											Anguish (RSPS) is a private server that is free to play.
											We strive ourselves on putting our players first. We listen and make discusses based on what our community decides. We are professional.
                    <br />
                </div>
            </div>
        </div>
        <div class="container images">
            <figure>
                <a href="#">
                    <span>Latest Media</span>
                    <img src="img/thumb_2.jpg" alt="" />
                </a>
            </figure>
            <figure>
                <a href="#">
                    <span>Latest Videos</span>
                    <img src="img/thumb_2.jpg" alt="" />
                </a>
            </figure>
            <figure>
                <a href="#">
                    <span>Server Info</span>
                    <img src="img/thumb_2.jpg" alt="" />
                </a>
            </figure>
            <figure>
                <a href="#">
                    <span>Lorem Ipsum</span>
                    <img src="img/thumb_2.jpg" alt="" />
                </a>
            </figure>
        </div>
        <footer class="bottom-footer">
            <p class="copyright">
                <span>Designed by <a href="http://art0fray.deviantart.com" target="_blank">Ray</a>.</span>
                <br />
                <span>All Rights Reserved. © 2014 | anguishps.com</span>
            </p>
            <p class="links">
                <a href="#">home</a> | <a href="#">community</a> | <a href="#">play now</a> | <a href="#">vote</a> | <a href="#">donations</a></p>
        </footer>
    </div>
</div>

<script src="js/global.js" rel="script" type="text/javascript"></script>
</body>
</html>