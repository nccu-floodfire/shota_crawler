<?php
include("member/check_login.php"); // 登入安全機制
require("crawler/crawler.php");
?>
<!DOCTYPE HTML>
<!--
    udn_crawler首頁
    TXT by HTML5 UP
    html5up.net | @n33co
    Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
    <head>
        <title>Search - udn_crawler</title>
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
                        <h1><a href="index.php" id="logo">Search</a></h1>
                        <p>udn聯合知識庫[定址會員]搜尋</p>
                        </br><p><font color=#006400>
                            <?php
                            echo $_SESSION['session_id'] . " 您好"
                            ?>
                        </font></p>
                    </div>
                </div>
            </header>

            <!-- Nav -->
            <nav id="nav">
                <ul>
                    <li class="current"><a href="index.php">Search</a></li>
                    <li><a href="crawling.php">Crawling Case</a></li>
                    <!--<li><a href="Logout">Logout</a></li>-->
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
                                    <!-- 查詢結果連結 -->
                                    <?php
                                    if (isset($_POST['submit'])) { // 如果使用者按過送出查詢
                                        if (!empty($_POST['SearchString'])) { // 至少要有第一個關鍵字
                                            if ($_POST['united'] != 1 && $_POST['economic'] != 1 && $_POST['minsen'] != 1 && $_POST['united_late'] != 1 && $_POST['star'] != 1 && $_POST['upaper'] != 1 && $_POST['world'] != 1 && $_POST['europe'] != 1) {
                                                echo "<p><strong><font color=#FF6600>請至少選擇一種報系!!</font></strong></p>";
                                            } else {
                                                $c = new Crawler;
                                                $c->query_saver();  // 將使用者的查詢存進資料庫
                                                echo "<p><strong><font color=#FF6600><a href=http://140.119.164.218/~shota/udn_crawler/crawling.php>您的查詢已建立，請點我檢視目前的處理情況。</a></font></strong></p>";
                                            }
                                            /* if ($c->link != NULL) {
                                              echo "<p><a href=\"" . $c->link . "\" target=\"_blank\">國內報系查詢連結</a></p>";
                                              }
                                              if ($c->link_out != NULL) {
                                              echo "<p><a href=\"" . $c->link_out . "\" target=\"_blank\">海外報系查詢連結</a></p>";
                                              } */
                                        } else {
                                            echo "<p><strong><font color=#FF6600>請至少輸入一個關鍵字!!</font></strong></p>";
                                        }
                                    }
                                    ?>
                                    <header>
                                        <h2>搜尋 | Search</h2>
                                    </header>
                                    <section>
                                        <form action="index.php" method="POST">
                                            <!-- type這格的屬性, name這格的變數名稱, checked這格預設要被選 -->
                                            <p>關鍵字： 
                                                <input type="text" name="SearchString" />
                                            </p>
                                            <p>
                                                <input type="radio" name="operator_2" value="+" checked/>and
                                                <input type="radio" name="operator_2" value="/"/>or
                                                <input type="radio" name="operator_2" value="-"/>not
                                                <input type="text" name="SearchString_2" />
                                            </p>
                                            <p>
                                                <input type="radio" name="operator_3" value="+" checked/>and
                                                <input type="radio" name="operator_3" value="/" />or
                                                <input type="radio" name="operator_3" value="-"/>not
                                                <input type="text" name="SearchString_3" />
                                            </p>
                                            <p>
                                                <input type="radio" name="operator_4" value="+" checked/>and
                                                <input type="radio" name="operator_4" value="/" />or
                                                <input type="radio" name="operator_4" value="-"/>not
                                                <input type="text" name="SearchString_4" />
                                            </p>
                                            <p>日期：<br/>
                                                <!-- 選擇一，選從今天往後多久以前 -->
                                                <input type="radio" name="date" value="00" checked/>
                                                <strong><font color=#FF6600>←選擇後記得點我!!</font></strong>
                                                <select name="ago"> <!-- selected預設被選取 -->
                                                    <option value= 0 selected>今天</option>
                                                    <option value= 6 >近 7 天</option>
                                                    <option value= 29 >近 30 天</option>
                                                    <option value= 365 >近一年</option>
                                                    <option value= 1095 >近三年</option>
                                                    <option value= 2185 >近六年</option>
                                                    <option value= 3630 >近十年</option>
                                                    <option value= -1 >所有日期</option>
                                                    <!-- ※沒登入不能使用所有日期搜尋-->
                                                </select><br/>
                                                <!-- 選擇二，選一個時間區間-->
                                                <input type="radio" name="date" value="99" />
                                                <strong><font color=#FF6600>←或者點我輸入日期範圍</font></strong>
                                                </br>從(例：西元2014年12月16日，請輸入<strong><font color=#FF6600>20141216</font></strong>
                                                <input type="text" name="start" />
                                                到(例：西元2015年6月9日，請輸入<strong><font color=#FF6600>20150609</font></strong>
                                                <input type="text" name="end">
                                            </p>
                                            <p>資料來源：
                                                <input type="checkbox" name="united" value=1 checked/>聯合報
                                                <input type="checkbox" name="economic" value=1 checked/>經濟日報
                                                <input type="checkbox" name="minsen" value=1 checked/>民生報
                                                <input type="checkbox" name="united_late" value=1 checked/>聯合晚報
                                                <input type="checkbox" name="star" value=1 checked/>星報
                                                <input type="checkbox" name="upaper" value=1 checked/>Upaper
                                                <input type="checkbox" name="world" value=1 checked/>美洲世界日報
                                                <input type="checkbox" name="europe" value=1 checked/>歐洲日報
                                            </p>
                                            <p>
                                                <input type="submit" name="submit" value="查詢" />
                                                按下查詢後請耐心等候，謝謝!!
                                            </p>
                                        </form>
                                        <p>民生報自2006年11月30日起停刊，仍可查得1978年2月18日至2006年11月30日期間的所有民生報資料。
                                            </br>星報自2006年11月1日起停刊，仍可查得1999年9月1日至2006年10月31日期間的所有星報資料。
                                            </br>歐洲日報自2009年09月01日起停刊，仍可查得2000年2月1日至2009年8月31日期間，聯合知識庫收錄的歐洲日報資料。
                                            </br>歐洲日報、美洲世界日報資料因時差關係，入庫時間延後一天。
                                        </p>
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