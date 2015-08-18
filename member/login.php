<!DOCTYPE HTML>
<!--
輸入帳號和密碼的網頁
-->
<?php
// 驗證會員
session_start(); // 使用session
header("Content-Type:text/html; charset=utf-8"); // 指定編碼
require('config.php');

$Account = $_POST['Account'];
$Password = $_POST['Password'];
?>
<html>
    <head>
        <title>Login</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <!--[if lte IE 8]><script src="assets/js/ie/html5shiv.js"></script><![endif]-->
        <link rel="stylesheet" href="../assets/css/main.css" />
        <!--[if lte IE 8]><link rel="stylesheet" href="assets/css/ie8.css" /><![endif]-->
    </head>
    <body>
        <div id="page-wrapper">

            <!-- Header -->
            <header id="header">
                <div class="logo container">
                    <div>
                        <h1><a href="index.php" id="logo">Login</a></h1>
                        <p></p>
                    </div>
                </div>
            </header>

            <!-- Nav -->
            <nav id="nav">
                <ul>
                    <li class="current"><a href="login.php">Login</a></li>
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
                                    <section>
                                        <?php
                                        if ( isset($_SESSION['session_id']) ) { // 代表已經登入
                                            header('Location:http://140.119.164.218/~shota/udn_crawler/crawling.php'); // 轉跳目前情況
                                        }
                                        if (empty($Account) || empty($Password)) {
                                            ?>
                                            <form action="login.php" method="POST">
                                                <!--
                                                    當提交表單時，表單數據會提交到名為"login.php" 的頁面(本頁面)：
                                                -->
                                                <p align = "center">Account: <input type="text" name="Account" /></p>
                                                <p align = "center">Password: <input type="password" name="Password" /></p>
                                                <p align = "center"><input type="submit" name="submit" value="Login" /></p>
                                            </form>
                                            <?php
                                        } else {
                                            $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
                                            $mysqli->set_charset("utf8"); // 連線使用UTF-8
                                            //==========連線測試
                                            if (mysqli_connect_errno()) {
                                                printf("<p>抱歉，連線失敗: %s<br>========================================<br>", mysqli_connect_error());
                                            } else {
                                                printf("<p>恭喜，連線成功!!<br>========================================<br>");
                                            }
                                            //==========登入驗證
                                            $query = "SELECT `Password` FROM `Udn_Users` WHERE binary `Account` = '$Account'";
                                            $result = $mysqli->query($query) or die($mysqli->error . __LINE__);
                                            printf("驗證中...請稍後<br>");
                                            if ($result->num_rows > 0) {
                                                $row = $result->fetch_assoc();
                                                $User_Password = $row['Password'];
                                                //==========close connection
                                                $mysqli->close();
                                                // 密碼核對
                                                if ($User_Password == md5($Password)) {
                                                    echo "<strong><font color=#FF6600>登入成功!!</font></strong><br>3秒後自動轉跳首頁...";
                                                    $_SESSION['session_id'] = $Account;
                                                    // 轉跳至目前情況
                                                    header("refresh: 3; url=http://140.119.164.218/~shota/udn_crawler/crawling.php");
                                                } else {
                                                    echo "<strong><font color=#FF6600>密碼錯誤!!</font></strong>請重新登入!!<br>請稍等5秒...";
                                                    // 轉跳, 重新登入
                                                    header("refresh: 5; url=http://140.119.164.218/~shota/udn_crawler/member/login.php");
                                                }
                                            } else {
                                                //==========close connection
                                                $mysqli->close();
                                                echo "<strong><font color=#FF6600>查無此帳號!!</font></strong>請重新登入!!<br>請稍等5秒...";
                                                // 轉跳, 重新登入
                                                header("refresh: 5; url=http://140.119.164.218/~shota/udn_crawler/member/login.php");
                                            }
                                        }
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
        <script src="../assets/js/jquery.min.js"></script>
        <script src="../assets/js/jquery.dropotron.min.js"></script>
        <script src="../assets/js/skel.min.js"></script>
        <script src="../assets/js/skel-viewport.min.js"></script>
        <script src="../assets/js/util.js"></script>
        <!--[if lte IE 8]><script src="assets/js/ie/respond.min.js"></script><![endif]-->
        <script src="../assets/js/main.js"></script>

    </body>
</html>