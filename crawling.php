<?php
include("member/check_login.php"); // 登入安全機制
require("crawler/show_case.php");
?>
<!DOCTYPE HTML>
<!--
        TXT by HTML5 UP
        html5up.net | @n33co
        Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
    <head>
        <title>Crawling Case - udn_crawler</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <!--[if lte IE 8]><script src="assets/js/ie/html5shiv.js"></script><![endif]-->
        <link rel="stylesheet" href="assets/css/main.css" />
        <!--[if lte IE 8]><link rel="stylesheet" href="assets/css/ie8.css" /><![endif]-->
    </head>
    <body>
        <div id="page-wrapper">

            <!-- Header -->
            <header id="header">
                <div class="logo container">
                    <div>
                        <h1><a href="crawling.php" id="logo">Crawling Case</a></h1>
                        <p>目前情況</p>
                    </div>
                </div>
            </header>

            <!-- Nav -->
            <nav id="nav">
                <ul>
                    <li><a href="index.php">Search</a></li>
                    <li class="current"><a href="crawling.php">Crawling Case</a></li>
                    <!--<li><a href="right-sidebar.php">Right Sidebar</a></li>-->
                    <!--<li><a href="finished.php">Finished Case</a></li>-->
                </ul>
            </nav>

            <!-- Main -->
            <div id="main-wrapper">
                <div id="main" class="container">
                    <div class="row">
                        <div class="12u">
                            <div class="content">
                                <!-- Content -->
                                <article class="box page-content">

                                    <header>
                                        <h2>目前情況</h2>
                                    </header>

                                    <section>
                                        <?php
                                        $c = new Show_case();
                                        $c->Show_table(); // 顯示目前資料庫中Case的狀態
                                        ?>
                                    </section>

                                </article>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer id="footer" class="container">
                <!-- Copyright -->
                <div id="copyright">
                    <ul class="menu">
                        <li>&copy; Shota@Flood Fire. All rights reserved</li><li>Design: <a href="http://html5up.net">HTML5 UP</a></li>
                    </ul>
                </div>

            </footer>

        </div>

        <!-- Scripts -->
        <script src="assets/js/jquery.min.js"></script>
        <script src="assets/js/jquery.dropotron.min.js"></script>
        <script src="assets/js/skel.min.js"></script>
        <script src="assets/js/skel-viewport.min.js"></script>
        <script src="assets/js/util.js"></script>
        <!--[if lte IE 8]><script src="assets/js/ie/respond.min.js"></script><![endif]-->
        <script src="assets/js/main.js"></script>

    </body>
</html>