<?php
/* 
驗證會員的檔案(目前已被login.php取代)
 */
    session_start(); // 使用session
    header("Content-Type:text/html; charset=utf-8"); // 指定編碼
    require('config.php');
 
    $Account = $_POST['Account'];
    $Password = $_POST['Password'];
 
    if ( empty($Account) || empty($Password) )
    {
        // 轉跳回原登入頁面
        header('Location:http://140.119.164.218/~shota/udn_crawler/member/login.php');
    }else{
        $mysqli = new mysqli( $db_host, $db_user, $db_pass, $db_name );
        $mysqli->set_charset("utf8"); // 連線使用UTF-8
        //==========連線測試
        if (mysqli_connect_errno()) {
            //printf("<p>抱歉，連線失敗: %s<br>========================================<br>", mysqli_connect_error());
            exit();
        } else {
            //printf("<p>恭喜，連線成功!!<br>========================================<br>");
        }
        //==========登入驗證
        $query = "SELECT `Password` FROM `Udn_Users` WHERE binary `Account` = '$Account'";
        $result = $mysqli->query($query) or die($mysqli->error . __LINE__);
        if ( $result->num_rows > 0 ) {
            //printf( "驗證中...請稍後<br>" );
            $row = $result->fetch_assoc();
            $User_Password = $row['Password'];
            // 密碼核對
            if ( $User_Password == md5($Password) ) {
                printf( "<script language='javascript'> alert(\"登入成功!!\"); </script>" );
                //$cookie_expiry = ( time() + 3600 ); // 一小時內不需重新登入
                // Just use / as the cookie path. That way your cookie will be available to your entire domain.
                //setcookie( "cookie_id", md5($Account.$Password), $cookie_expiry, "/" );
                // 轉跳至首
                header('Location:http://140.119.164.218/~shota/udn_crawler/index.php');
            }else{
                printf( "<script language='javascript'> alert(\"密碼錯誤!!\"); </script>" );
                printf( "<a href=\"http://140.119.164.218/~shota/udn_crawler/member/login.php\">重新登入</a>");
                // 轉跳回原登入頁面
                //header('Location:http://140.119.164.218/~shota/udn_crawler/member/login.php');
            }
        }else{
            printf( "<script language='javascript'> alert(\"查無此帳號!!\"); </script>" );
            printf( "<a href=\"http://140.119.164.218/~shota/udn_crawler/member/login.php\">重新登入</a>");
            // 轉跳回原登入頁面
            //header('Location:http://140.119.164.218/~shota/udn_crawler/member/login.php');
        }
    }
    
    //==========close connection
    $mysqli->close();
?>