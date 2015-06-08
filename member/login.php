<!DOCTYPE html>
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
        <meta charset="UTF-8">
        <title>Login</title>
    </head>
    
    <body>
        <?php
        if ( empty($Account) || empty($Password) ) {
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
        }else{
            $mysqli = new mysqli( $db_host, $db_user, $db_pass, $db_name );
            $mysqli->set_charset("utf8"); // 連線使用UTF-8
            //==========連線測試
            if (mysqli_connect_errno()) {
                printf("<p>抱歉，連線失敗: %s<br>========================================<br>", mysqli_connect_error());
            }else{
                printf("<p>恭喜，連線成功!!<br>========================================<br>");
            }
            //==========登入驗證
            $query = "SELECT `Password` FROM `Udn_Users` WHERE binary `Account` = '$Account'";
            $result = $mysqli->query($query) or die($mysqli->error . __LINE__);
            printf( "驗證中...請稍後<br>" );
            if ( $result->num_rows > 0 )
            {
                $row = $result->fetch_assoc();
                $User_Password = $row['Password'];
                //==========close connection
                $mysqli->close();
                // 密碼核對
                if ( $User_Password == md5($Password) )
                {
                    printf("登入成功!!<br>3秒後自動轉跳首頁...");
                    $_SESSION['session_id'] = md5($Account.$Password);
                    // 轉跳至首頁
                    header("refresh: 3; url=http://140.119.164.218/~shota/udn_crawler/index.php");
                }else{
                    printf("密碼錯誤!!請重新登入!!<br>請稍等5秒...");
                    // 轉跳, 重新登入
                    header("refresh: 5; url=http://140.119.164.218/~shota/udn_crawler/member/login.php");
                }
            }else{
                printf("查無此帳號!!請重新登入!!<br>請稍等5秒...");
                // 轉跳, 重新登入
                header("refresh: 5; url=http://140.119.164.218/~shota/udn_crawler/member/login.php");
            }
        }
        ?>
    </body>
</html>